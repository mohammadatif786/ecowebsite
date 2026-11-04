<template>
    <div class="qna-container">
        <!-- Question Input (Audience Only) -->
        <div v-if="!isHost && streamId" class="qna-input-box">
            <input type="text" v-model="questionInput" placeholder="Ask a question..." @keydown.enter="submitQuestion"
                :disabled="qnaCooldown">
            <button class="btn-sm" style="background:var(--success); color:#fff;" @click="submitQuestion"
                :disabled="qnaCooldown">
                {{ qnaCooldown ? 'Wait...' : 'Ask' }}
            </button>
        </div>

        <!-- Questions List -->
        <div v-if="streamId" id="qna-list" class="qna-list">
            <div v-if="!hasQuestions" style="text-align:center; color:#aaa; padding:20px;">
                {{ isHost ? 'No questions yet' : 'Be the first to ask!' }}
            </div>

            <div v-for="(q, index) in questions" :key="`qna-${q.id}-${index}`" class="qna-card"
                :class="{ answered: q.answered }">
                <div class="qna-header">
                    <span class="qna-user">{{ typeof q.from === 'object' ? q.from.name : (q.from || 'User') }}</span>
                    <span v-if="q.answered" class="qna-badge">✓ Answered</span>
                </div>
                <div class="qna-text">{{ q.text }}</div>
                <div v-if="isHost && !q.answered" class="qna-actions">
                    <button class="btn-sm btn-answer" @click="answerQuestion(q.id)">Mark Answered</button>
                    <button class="btn-sm btn-delete" @click="deleteQuestion(q.id)">Delete</button>
                </div>
            </div>
        </div>

        <!-- Stream Ended Message -->
        <div v-if="!streamId" style="text-align:center; color:#aaa; padding:20px;">
            No Questions yet.
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick, computed } from 'vue'
import { toast } from 'vue-sonner'
import axios from 'axios'

interface QnaQuestion {
    id: string
    text: string
    from: string | { id: number; name: string }
    answered: boolean
    timestamp?: number
}

const props = defineProps<{
    streamId: string | number | null
    isHost: boolean
    sessionEvent?: { name: string; payload: any } | null
}>()

// Q&A state
const questions = ref<QnaQuestion[]>([])
const questionInput = ref('')
const qnaCooldown = ref(false)

// Computed property to ensure reactivity
const hasQuestions = computed(() => questions.value.length > 0)
const questionsCount = computed(() => questions.value.length)

// Methods
const submitQuestion = async () => {
    const text = questionInput.value.trim()

    if (!props.streamId) {
        toast.error("Stream is not available.")
        return
    }

    if (!text) {
        toast.error("Please enter a question.")
        return
    }

    if (qnaCooldown.value) {
        toast.warning("Wait 5 seconds before submitting another question.")
        return
    }

    try {
        const response = await axios.post(
            route('frontend.live.qna.submit', { stream: props.streamId }),
            { text }
        )

        if (response.data?.ok && response.data.qna) {

            const quest = response.data.qna

            questions.value.push({
                id: String(quest.id),
                text: quest.text,
                from: { id: quest.from.id, name: quest.from.name },
                answered: quest.answered || false,
                timestamp: Date.now()
            });
            questionInput.value = ''
            qnaCooldown.value = true
            window.setTimeout(() => { qnaCooldown.value = false }, 5000)
        }
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Failed to submit question')
    }
}

const answerQuestion = async (questionId: string) => {
    if (!props.streamId) {
        return
    }

    try {
        const response = await axios.post(
            route('frontend.live.qna.answer', { stream: props.streamId, qna: questionId })
        )

        if (response.data?.ok) {
            const question = questions.value.find(q => String(q.id) === String(questionId))
            if (question) {
                question.answered = true
            }
        }
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Failed to answer question')
    }
}

const deleteQuestion = async (questionId: string) => {
    if (!props.streamId) {
        return
    }

    try {
        const response = await axios.delete(
            route('frontend.live.qna.delete', { stream: props.streamId, qna: questionId })
        )

        if (response.data?.ok) {
            const questionIndex = questions.value.findIndex(q => String(q.id) === String(questionId))
            if (questionIndex !== -1) {
                questions.value.splice(questionIndex, 1)
            }
        }
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Failed to delete question')
    }
}

const fetchQna = async () => {
    if (!props.streamId) {
        return
    }

    try {
        const stateRes = await axios.get(
            route('frontend.live.state', { stream: props.streamId })
        )
        if (stateRes.data?.ok && Array.isArray(stateRes.data.qna)) {
            questions.value = stateRes.data.qna.map((q: any) => ({
                id: String(q.id),
                text: q.text,
                from: q.from,
                answered: q.answered || false,
                timestamp: q.timestamp || Date.now()
            }));
        }
    } catch (e: any) {
        console.error('QnaComponent: Failed to fetch Q&A:', e)
    }
}

const subscribeToQnaEvents = () => {
    // The page parent owns the sole private Echo subscription and forwards events.
    return

    if (!props.streamId) {
        return
    }
    if (!window.Echo) {
        return
    }

    const channel = 'live.' + String(props.streamId)

    const echoChannel = window.Echo.channel(channel)

    echoChannel
        .listen('.QnaSubmitted', (e: any) => {
            if (questions.value.some(q => String(q.id) === String(e.id))) {
                return;
            }

            const newQuestion: QnaQuestion = {
                id: String(e.id),
                text: e.text,
                from: e.from,
                answered: false,
                timestamp: e.timestamp || Date.now()
            };

            questions.value.unshift(newQuestion);

            if (props.isHost) {
                toast.info(`New question from ${e.from.name || 'User'}`);
            }
        })
        .listen('.QnaAnswered', (e: any) => {

            const question = questions.value.find(q => String(q.id) === String(e.id))

            if (question) {
                question.answered = true
                toast.info('Question marked as answered')
            }
        })
        .listen('.QnaDeleted', (e: any) => {
            const questionIndex = questions.value.findIndex(q => String(q.id) === String(e.id))

            if (questionIndex !== -1) {
                questions.value.splice(questionIndex, 1)

                if (props.isHost) {
                    toast.info('Question deleted')
                }
            }
        })
        .listen('.StreamEnded', (e: any) => {
            const eventStreamId = String(e.id || e.stream_id || '')

            if (props.streamId && String(props.streamId) === eventStreamId) {
                questions.value = []
                questionInput.value = ''
                qnaCooldown.value = false
            }
        })
}

watch(() => props.streamId, async (newStreamId, oldStreamId) => {

    if (newStreamId) {
        if (oldStreamId === undefined || newStreamId !== oldStreamId) {
            await fetchQna()
            subscribeToQnaEvents()
        }
    } else if (oldStreamId !== undefined) {
        questions.value = []
        questionInput.value = ''
        qnaCooldown.value = false
    }
}, { immediate: true })

watch(() => props.sessionEvent, (event) => {
    if (!event) return
    const payload = event.payload
    if (event.name === 'QnaSubmitted' && !questions.value.some(question => String(question.id) === String(payload.id))) {
        questions.value.unshift({ id: String(payload.id), text: payload.text, from: payload.from, answered: false, timestamp: payload.timestamp || Date.now() })
    } else if (event.name === 'QnaAnswered') {
        const question = questions.value.find(question => String(question.id) === String(payload.id))
        if (question) question.answered = true
    } else if (event.name === 'QnaDeleted') {
        questions.value = questions.value.filter(question => String(question.id) !== String(payload.id))
    } else if (event.name === 'StreamEnded') {
        questions.value = []
    }
})

onMounted(async () => {
    if (props.streamId) {
        await new Promise(resolve => setTimeout(resolve, 300))
        await fetchQna()
        subscribeToQnaEvents()
    }
})
onUnmounted(() => {
    // Leave Echo channel on unmount
})
</script>

<style scoped>
.qna-container { color:#fff; }
.qna-container::before { display:block; margin-bottom:12px; color:#f8fafc; content:'Questions & Answers'; font-size:14px; font-weight:900; letter-spacing:-.01em; }
.qna-input-box { display:flex; gap:8px; margin-bottom:14px; padding:10px; border:1px solid rgba(255,255,255,.1); border-radius:16px; background:rgba(255,255,255,.055); }
.qna-input-box input { min-width:0; flex:1; border:1px solid rgba(255,255,255,.09); border-radius:11px; background:rgba(0,0,0,.2); padding:10px 11px; color:#fff; font-size:11px; outline:none; }
.qna-input-box input::placeholder { color:#7e89a8; }
.qna-input-box input:focus { border-color:#60a5fa; box-shadow:0 0 0 3px rgba(59,130,246,.12); }
.qna-input-box .btn-sm { flex:none; border:0; border-radius:11px; background:linear-gradient(135deg,#2563eb,#7c3aed) !important; padding:0 14px; color:#fff; font-size:10px; font-weight:900; }
.qna-input-box .btn-sm:disabled { cursor:not-allowed; opacity:.45; }
.qna-list { display:flex; flex-direction:column; gap:9px; }
.qna-card { border:1px solid rgba(255,255,255,.09); border-radius:16px; background:rgba(255,255,255,.05); padding:12px; box-shadow:0 8px 24px rgba(0,0,0,.08); transition:border-color .2s, background .2s, transform .2s; }
.qna-card:hover { transform:translateY(-1px); border-color:rgba(96,165,250,.36); background:rgba(255,255,255,.075); }
.qna-card.answered { border-color:rgba(52,211,153,.22); background:rgba(16,185,129,.07); }
.qna-header { display:flex; align-items:center; justify-content:space-between; gap:8px; }
.qna-user { position:relative; min-width:0; overflow:hidden; padding-left:37px; color:#f8fafc; font-size:11px; font-weight:900; line-height:30px; text-overflow:ellipsis; white-space:nowrap; }
.qna-user::before { position:absolute; top:0; left:0; display:grid; width:30px; height:30px; place-items:center; border-radius:10px; background:linear-gradient(135deg,#2563eb,#7c3aed); color:#fff; content:'Q'; font-size:10px; font-weight:900; }
.qna-badge { flex:none; border-radius:999px; background:rgba(16,185,129,.14); padding:5px 8px; color:#6ee7b7; font-size:0; font-weight:900; text-transform:uppercase; }
.qna-badge::after { content:'✓ Answered'; font-size:8px; }
.qna-text { margin-top:11px; color:#dbe5ff; font-size:12px; font-weight:650; line-height:1.55; overflow-wrap:anywhere; }
.qna-actions { display:flex; align-items:center; gap:7px; margin-top:11px; padding-top:9px; border-top:1px solid rgba(255,255,255,.065); }
.qna-actions .btn-sm { border:0; border-radius:10px; padding:8px 10px; font-size:9px; font-weight:900; transition:background .2s; }
.btn-answer { flex:1; background:rgba(16,185,129,.14); color:#6ee7b7; }
.btn-answer:hover { background:rgba(16,185,129,.24); }
.btn-delete { background:rgba(244,63,94,.12); color:#fda4af; }
.btn-delete:hover { background:rgba(244,63,94,.23); }
.qna-list > div:first-child:not(.qna-card) { min-height:145px; display:grid; place-items:center; border:1px dashed rgba(148,163,184,.2); border-radius:16px; background:rgba(255,255,255,.025); color:#8290b3 !important; font-size:11px; font-weight:800; }
</style>

<template>
    <div class="poll-container">
        <!-- Poll Creator (Host Only) - Hidden when poll is active or stream ended -->
        <div v-if="isHost && !activePoll && streamId" class="poll-creator">
            <h4 style="margin:0 0 12px 0;">Create Poll</h4>
            <input type="text" v-model="pollQuestion" placeholder="Question...">
            <input type="text" v-model="pollOptions[0]" placeholder="Option 1">
            <input type="text" v-model="pollOptions[1]" placeholder="Option 2">
            <input type="text" v-model="pollOptions[2]" placeholder="Option 3">
            <input type="text" v-model="pollOptions[3]" placeholder="Option 4">
            <button class="btn-pill" style="width:100%; margin-top:12px; font-size:12px; color:#2fa4e6"
                @click="createPoll">Launch Poll</button>
        </div>

        <!-- Active Poll Display -->
        <div v-if="activePoll && streamId" class="poll-display" :style="{ marginTop: isHost ? '0' : '0' }">
            <strong style="display:block; margin-bottom:10px;">
                {{ activePoll.question }}
            </strong>

            <div v-for="(opt, idx) in activePoll.options" :key="idx" class="poll-option"
                :class="{ voted: userVotedOption === idx }" @click="vote(activePoll.id, idx)"
                style="cursor:pointer;">

                <div style="font-size:12px; display:flex; justify-content:space-between;">
                    <span>{{ opt.text }}</span>
                    <span>{{ opt.votes }} vote{{ opt.votes !== 1 ? 's' : '' }} ({{ totalVotes > 0 ? ((opt.votes / totalVotes) * 100).toFixed(0) : 0 }}%)</span>
                </div>

                <div class="poll-bar-bg">
                    <div class="poll-bar-fill"
                        :style="{ width: totalVotes > 0 ? `${(opt.votes / totalVotes) * 100}%` : '0%' }">
                    </div>
                </div>
            </div>

            <button v-if="isHost" @click="endPoll(activePoll.id)"
                style="background:transparent; border:none; color:var(--danger); font-size:11px; cursor:pointer; margin-top:8px;">
                End Poll
            </button>
        </div>

        <!-- No Active Poll Message -->
        <div v-else-if="!isHost && streamId" style="text-align:center; color:#aaa; padding:20px;">
            No active poll
        </div>

        <!-- Stream Ended Message -->
        <div v-if="!streamId" style="text-align:center; color:#aaa; padding:20px;">
            No polls yet.
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { toast } from 'vue-sonner'
import axios from 'axios'
import { Poll } from './liveInterface'

const props = defineProps<{
    streamId: string | number | null
    isHost: boolean
    sessionEvent?: { name: string; payload: any } | null
}>()

const polls = ref<Poll[]>([])
const pollQuestion = ref('')
const pollOptions = ref(['', '', '', ''])
const userVotedOption = ref<number | null>(null)

const activePoll = computed(() => polls.value.find(p => p.active))
const totalVotes = computed(() => {
    return activePoll.value?.options.reduce((s, o) => s + o.votes, 0) ?? 0
})

const createPoll = async () => {
    const question = pollQuestion.value.trim()
    const options = pollOptions.value.filter(opt => opt.trim()).map(opt => ({ text: opt.trim(), votes: 0 }))

    if (!props.streamId) {
        toast.error("Start the live stream before creating a poll.")
        return
    }
    if (!question || options.length < 2) {
        toast.error("Fill in question and at least 2 options.")
        return
    }

    pollQuestion.value = ''
    pollOptions.value = ['', '', '', '']

    try {
        const response = await axios.post(route('frontend.live.poll.create', { stream: props.streamId }), {
            question,
            options: options.map(o => o.text)
        })

        if (response.data?.ok) {
            if (response.data.poll) {
                polls.value = polls.value.filter(p => !p.active)
                polls.value.push({
                    id: String(response.data.poll.id),
                    question: response.data.poll.question,
                    options: (response.data.poll.options || []).map((opt: any) => ({
                        text: opt.text,
                        votes: opt.votes || 0
                    })),
                    active: true
                })
            }
        }
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Failed to create poll')
    }
}

const endPoll = async (pollId: string) => {
    const poll = polls.value.find(p => String(p.id) === String(pollId))
    if (!poll) {
        toast.error('Poll not found')
        return
    }
    poll.active = false

    try {
        const response = await axios.post(route('frontend.live.poll.end', { stream: props.streamId, poll: pollId }))
        if (response.data?.ok) {

            if (props.isHost) {
                pollQuestion.value = ''
                pollOptions.value = ['', '', '', '']
            }
            userVotedOption.value = null
        }
    } catch (error: any) {
        poll.active = true
        console.error('Failed to end poll:', error)
    }
}

const vote = async (pollId: any, optionIndex: any) => {
    if (userVotedOption.value !== null) {
        toast.warning('You have already voted!')
        return
    }

    const poll = activePoll.value
    if (!poll) {
        toast.error('No active poll found')
        return
    }
    userVotedOption.value = optionIndex

    try {
        await axios.post(
            route('frontend.live.poll.vote', { stream: props.streamId, poll: pollId }),
            {
                option_id: optionIndex + 1
            }
        )

    } catch (error: any) {
        userVotedOption.value = null
        toast.error(error.response?.data?.message || 'Vote failed to register. Please try again.')
    }
}

const fetchActivePoll = async () => {
    if (!props.streamId) {
        return
    }

    try {
        const stateRes = await axios.get(route('frontend.live.state', { stream: props.streamId }))

        if (stateRes.data?.ok && stateRes.data.active_poll) {

            const poll = stateRes.data.active_poll
            polls.value = polls.value.filter(p => !p.active)
            const pollOptions = Array.isArray(poll.options) ? poll.options : []

            const newPoll = {
                id: String(poll.id),
                question: poll.question,
                options: pollOptions.map((opt: any) => ({
                    text: opt.text || String(opt),
                    votes: Number(opt.votes) || 0
                })),
                active: true
            }

            polls.value.push(newPoll)
            userVotedOption.value = poll.user_voted_option === null || poll.user_voted_option === undefined
                ? null
                : Number(poll.user_voted_option)
        }
    } catch (e: any) {
        console.error('PollComponent: Failed to fetch active poll:', e)
    }
}

const subscribeToPollEvents = () => {
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
        .listen('.PollCreated', (e: any) => {
             if (!e.id || !e.question) {
                return
            }
            polls.value = polls.value.filter(p => !p.active)
            const pollOptions = Array.isArray(e.options) ? e.options : []

            const newPoll = {
                id: String(e.id),
                question: e.question,
                options: pollOptions.map((opt: any) => ({
                    text: opt.text || opt,
                    votes: Number(opt.votes) || 0
                })),
                active: true
            }

            polls.value.push(newPoll)

        })
        .listen('.PollVoted', (e: any) => {
            const poll = polls.value.find(p => {
                const pollId = String(p.id)
                const eventPollId = String(e.poll_id)
                 return pollId === eventPollId && p.active
            })

            if (!poll) {
                return
            }
            if (e.votes && Array.isArray(e.votes)) {
                poll.options = e.votes.map((opt: any) => ({
                    text: opt.text,
                    votes: Number(opt.votes) || 0
                }))
             } else if (poll.options && poll.options[e.option_id - 1]) {
                poll.options[e.option_id - 1].votes = (poll.options[e.option_id - 1].votes || 0) + 1
            }
        })
        .listen('.PollEnded', (e: any) => {

             const pollIdToEnd = String(e.poll_id || e.pollId)
             const pollIndex = polls.value.findIndex(p => String(p.id) === pollIdToEnd)

            if (pollIndex !== -1) {

                polls.value.splice(pollIndex, 1)

                if (props.isHost) {
                    pollQuestion.value = ''
                    pollOptions.value = ['', '', '', '']
                }
                userVotedOption.value = null
            } else {
                userVotedOption.value = null
            }
        })
        .listen('.StreamEnded', (e: any) => {
            const eventStreamId = String(e.id || e.stream_id || '')

            const shouldClear = props.streamId && (
                String(props.streamId) === eventStreamId ||
                eventStreamId === ''
            )

            if (shouldClear || !props.streamId) {
                 polls.value = []
                userVotedOption.value = null
                if (props.isHost) {
                    pollQuestion.value = ''
                    pollOptions.value = ['', '', '', '']
                }
            }
        })
}

watch(() => props.streamId, async (newId, oldId) => {
    if (newId) {
        if (oldId === undefined || newId !== oldId) {
            await fetchActivePoll()
            subscribeToPollEvents()
        }
    } else {
        polls.value = []
        userVotedOption.value = null
        if (props.isHost) {
            pollQuestion.value = ''
            pollOptions.value = ['', '', '', '']
        }
    }
}, { immediate: true })

watch(() => props.sessionEvent, (event) => {
    if (!event) return
    const payload = event.payload
    if (event.name === 'PollCreated') {
        polls.value = [{ id: String(payload.id), question: payload.question, options: (payload.options || []).map((option: any) => ({ text: option.text || option, votes: Number(option.votes) || 0 })), active: true }]
    } else if (event.name === 'PollVoted') {
        const poll = polls.value.find(item => String(item.id) === String(payload.poll_id))
        if (poll && Array.isArray(payload.votes)) poll.options = payload.votes.map((option: any) => ({ text: option.text, votes: Number(option.votes) || 0 }))
    } else if (event.name === 'PollEnded' || event.name === 'StreamEnded') {
        polls.value = event.name === 'StreamEnded' ? [] : polls.value.filter(item => String(item.id) !== String(payload.poll_id))
    }
})

onMounted(async () => {
   if (props.streamId) {
        await new Promise(resolve => setTimeout(resolve, 300))
        await fetchActivePoll()
        subscribeToPollEvents()
    }
})

onUnmounted(() => {
    // Cleanup is handled by Echo automatically when component unmounts
})
</script>

<style scoped>
.poll-container { color: #fff; }
.poll-creator, .poll-display { border: 1px solid rgba(255,255,255,.1); border-radius: 18px; background: rgba(255,255,255,.06); padding: 14px; }
.poll-creator input { width: 100%; margin-top: 8px; border: 1px solid rgba(255,255,255,.12); border-radius: 12px; background: rgba(0,0,0,.22); padding: 10px 12px; color: #fff; outline: none; }
.poll-creator input:focus { border-color: #3b82f6; }
.btn-pill { border: 0; border-radius: 999px; background: #2563eb; padding: 10px 14px; color: #fff !important; font-weight: 800; }
.poll-option { margin-top: 10px; border: 1px solid rgba(255,255,255,.1); border-radius: 14px; background: rgba(255,255,255,.05); padding: 10px; transition: .2s ease; }
.poll-option:hover { border-color: rgba(96,165,250,.8); background: rgba(59,130,246,.12); }
.poll-option.voted { border-color: #60a5fa; background: rgba(59,130,246,.2); }
.poll-bar-bg { height: 6px; margin-top: 8px; overflow: hidden; border-radius: 999px; background: rgba(255,255,255,.1); }
.poll-bar-fill { height: 100%; border-radius: inherit; background: linear-gradient(90deg,#3b82f6,#8b5cf6); transition: width .3s ease; }
</style>

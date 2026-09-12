<script setup lang="ts">
import { QuillEditor } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";
import { defineProps, defineEmits, reactive } from "vue";

// 1. Accept props and emits
const props = defineProps<{
    modelValue: string;
    class?: string;
    placeholder?: string
}>();

const emit = defineEmits<{
    (e: "update:modelValue", value: string): void;
}>();

const onEditorChange = (html: any) => {
    emit("update:modelValue", html);
    // console.log(html);
};

const editorOption = reactive({
    placeholder: props.placeholder || "Enter content here ...",
    modules: {
        toolbar: [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{ header: 1 }, { header: 2 }],
            [{ list: 'ordered' }, { list: 'bullet' }],
            [{ script: 'sub' }, { script: 'super' }],
            [{ indent: '-1' }, { indent: '+1' }],
            [{ direction: 'rtl' }],
            [{ size: ['small', false, 'large', 'huge'] }],
            [{ header: [1, 2, 3, 4, 5, 6, false] }],
            [{ color: [] }, { background: [] }],
            [{ font: [] }],
            [{ align: [] }],
            ['clean'],
            // ['link', 'image', 'video']
            ['link']
        ]
    }
});
</script>


<template>
    <quill-editor :class="props.class" :content="modelValue" content-type="html"
    :options="editorOption" @update:content="onEditorChange" />
</template>

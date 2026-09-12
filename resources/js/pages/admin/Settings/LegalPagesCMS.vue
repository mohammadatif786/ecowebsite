<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

interface LegalPage {
    key: string;
    title: string;
    content: string;
    is_default: boolean;
    is_custom?: boolean;
    is_visible: boolean;
    updated_at: string | null;
}

const pages = ref<LegalPage[]>([]);
const isLoading = ref(false);
const isSavingKey = ref<string | null>(null);

// Add custom page state
const showAddSection = ref(false);
const newPageTitle = ref('');
const newPageKey = ref('');
const newPageContent = ref('');
const newPageIsVisible = ref(true);

const updateNewPageSlug = () => {
    newPageKey.value = newPageTitle.value
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)+/g, '');
};

const fetchPages = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/admin/settings/legal-pages');
        pages.value = response.data;
    } catch (e) {
        console.error('Failed fetching legal pages', e);
    } finally {
        isLoading.value = false;
    }
};

const savePage = async (page: LegalPage) => {
    isSavingKey.value = page.key;
    try {
        await axios.post('/admin/settings/legal-pages', {
            key: page.key,
            title: page.title,
            content: page.content,
            is_visible: page.is_visible,
        });
        alert(`"${page.title}" successfully saved!`);
        await fetchPages();
    } catch (e) {
        console.error(e);
        alert('Failed to save page.');
    } finally {
        isSavingKey.value = null;
    }
};

const deletePage = async (page: LegalPage) => {
    const confirmMsg = page.is_custom
        ? `Are you sure you want to delete the custom page "${page.title}"?`
        : `Are you sure you want to reset "${page.title}" to its system default content?`;

    if (!confirm(confirmMsg)) return;

    try {
        await axios.delete(`/admin/settings/legal-pages/${page.key}`);
        alert(page.is_custom ? 'Page deleted!' : 'Page reset to default!');
        await fetchPages();
    } catch (e) {
        console.error(e);
        alert('Failed to delete/reset page.');
    }
};

const createCustomPage = async () => {
    if (!newPageTitle.value || !newPageKey.value) {
        alert('Please fill out Title and Slug.');
        return;
    }

    if (pages.value.some(p => p.key === newPageKey.value)) {
        alert('A page with this slug key already exists.');
        return;
    }

    try {
        await axios.post('/admin/settings/legal-pages', {
            key: newPageKey.value,
            title: newPageTitle.value,
            content: newPageContent.value,
            is_visible: newPageIsVisible.value,
        });

        newPageTitle.value = '';
        newPageKey.value = '';
        newPageContent.value = '';
        newPageIsVisible.value = true;
        showAddSection.value = false;

        alert('Custom page created successfully!');
        await fetchPages();
    } catch (e) {
        console.error(e);
        alert('Failed to create custom page.');
    }
};

const handleFileUpload = (event: Event, page: LegalPage) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        const reader = new FileReader();
        reader.onload = (e) => {
            if (e.target && typeof e.target.result === 'string') {
                page.content = e.target.result;
            }
        };
        reader.readAsText(file);
    }
};

const handleNewPageFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        const reader = new FileReader();
        reader.onload = (e) => {
            if (e.target && typeof e.target.result === 'string') {
                newPageContent.value = e.target.result;
            }
        };
        reader.readAsText(file);
    }
};

onMounted(() => {
    fetchPages();
});
</script>

<template>
    <div style="max-width:820px;margin:0 auto;padding:10px">
        <!-- Header -->
        <h2 style="font-size:26px;font-weight:800;margin-bottom:4px">Legal &amp; Pages</h2>
        <p style="color:#64748b;margin-bottom:20px;font-size:14px;line-height:1.5">
            Physically input or upload your policy pages. Hidden pages return a 404 and disappear from the footer. Leave
            a default box blank/reset to use the built-in system fallback.
        </p>

        <!-- Actions -->
        <div style="margin-bottom:20px;display:none;justify-content:flex-end">
            <button @click="showAddSection = !showAddSection"
                style="background:#2f9bef;color:#fff;border:none;border-radius:10px;padding:8px 16px;font-weight:800;font-size:13px;cursor:pointer">
                {{ showAddSection ? 'Cancel Custom Page' : '+ Add Custom Page' }}
            </button>
        </div>

        <!-- Add Custom Page Form -->
        <div v-if="showAddSection"
            style="border:1px solid #e2e8f0;border-radius:16px;padding:20px;background:#fff;margin-bottom:24px">
            <h3 style="font-weight:800;font-size:16px;margin-bottom:16px">Create New Custom Legal Page</h3>

            <div style="margin-bottom:14px">
                <label style="display:block;font-size:12px;font-weight:800;color:#64748b;margin-bottom:6px">Page
                    Title</label>
                <input type="text" v-model="newPageTitle" @input="updateNewPageSlug" placeholder="e.g. Cookie Policy"
                    style="width:100%;border:1px solid #e2e8f0;border-radius:10px;padding:10px;font-size:13px" />
            </div>

            <div style="margin-bottom:14px">
                <label style="display:block;font-size:12px;font-weight:800;color:#64748b;margin-bottom:6px">Slug Key
                    (Route URL)</label>
                <input type="text" v-model="newPageKey" placeholder="cookie-policy"
                    style="width:100%;border:1px solid #e2e8f0;border-radius:10px;padding:10px;font-size:13px;font-family:monospace" />
                <span style="font-size:11px;color:#94a3b8">The page will be served at: /{{ newPageKey || 'slug'
                }}</span>
            </div>

            <div style="margin-bottom:14px;display:flex;align-items:center;justify-content:space-between">
                <div>
                    <label style="display:block;font-size:12px;font-weight:800;color:#64748b">Publish Status</label>
                    <span style="font-size:11px;color:#94a3b8">Show page immediately on creation</span>
                </div>
                <button type="button" @click="newPageIsVisible = !newPageIsVisible" :style="{
                    position: 'relative',
                    display: 'inline-flex',
                    height: '24px',
                    width: '44px',
                    borderRadius: '9999px',
                    border: 'none',
                    cursor: 'pointer',
                    transition: 'background-color 0.2s',
                    backgroundColor: newPageIsVisible ? '#2f9bef' : '#cbd5e1'
                }">
                    <span :style="{
                        display: 'inline-block',
                        height: '20px',
                        width: '20px',
                        borderRadius: '50%',
                        backgroundColor: '#fff',
                        boxShadow: '0 1px 3px 0 rgba(0, 0, 0, 0.1)',
                        transition: 'transform 0.2s',
                        transform: newPageIsVisible ? 'translateX(20px)' : 'translateX(0px)',
                        marginTop: '2px',
                        marginLeft: '2px'
                    }" />
                </button>
            </div>

            <div style="margin-bottom:16px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <label style="font-size:12px;font-weight:800;color:#64748b">Content</label>
                    <label
                        style="background:#f1f5f9;border-radius:10px;padding:6px 12px;font-size:11px;font-weight:800;cursor:pointer">⬆
                        Upload file<input type="file" accept=".txt,.md,.html,.htm,text/plain"
                            @change="handleNewPageFileUpload($event)" style="display:none" /></label>
                </div>
                <div style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
                    <QuillEditor v-model:content="newPageContent" contentType="html" theme="snow"
                        style="min-height: 200px;" />
                </div>
            </div>

            <button @click="createCustomPage"
                style="width:100%;background:#10b981;color:#fff;border:none;border-radius:10px;padding:11px;font-weight:800;font-size:13px;cursor:pointer">
                Create Legal Page
            </button>
        </div>

        <div v-if="isLoading" style="text-align:center;padding:40px 0;color:#64748b">
            Loading pages...
        </div>

        <!-- Vertical Catalog list of Page Cards -->
        <div v-else v-for="page in pages" :key="page.key"
            style="border:1px solid #e2e8f0;border-radius:16px;padding:20px;background:#fff;margin-bottom:20px">
            <!-- Card Header -->
            <div
                style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
                <div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="font-weight:800;font-size:16px">{{ page.title }}</span>
                        <span v-if="page.is_default"
                            style="font-size:9px;background:#f1f5f9;color:#64748b;padding:2px 8px;border-radius:999px;font-weight:800;text-transform:uppercase">
                            Default
                        </span>
                        <span v-else-if="page.is_custom"
                            style="font-size:9px;background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:999px;font-weight:800;text-transform:uppercase">
                            Custom
                        </span>
                        <span v-else
                            style="font-size:9px;background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:999px;font-weight:800;text-transform:uppercase">
                            CMS Override
                        </span>
                    </div>
                    <span style="font-size:11px;color:#94a3b8;font-family:monospace;display:block;margin-top:2px">Route:
                        /{{ page.key }}</span>
                </div>

                <div style="display:flex;align-items:center;gap:12px">
                    <!-- Visibility Status Switch -->
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="font-size:12px;font-weight:700;color:#64748b">{{ page.is_visible ? 'Visible' :
                            'Hidden' }}</span>
                        <button type="button" @click="page.is_visible = !page.is_visible" :style="{
                            position: 'relative',
                            display: 'inline-flex',
                            height: '20px',
                            width: '38px',
                            borderRadius: '9999px',
                            border: 'none',
                            cursor: 'pointer',
                            transition: 'background-color 0.2s',
                            backgroundColor: page.is_visible ? '#10b981' : '#cbd5e1'
                        }">
                            <span :style="{
                                display: 'inline-block',
                                height: '16px',
                                width: '16px',
                                borderRadius: '50%',
                                backgroundColor: '#fff',
                                boxShadow: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                                transition: 'transform 0.2s',
                                transform: page.is_visible ? 'translateX(18px)' : 'translateX(0px)',
                                marginTop: '2px',
                                marginLeft: '2px'
                            }" />
                        </button>
                    </div>

                    <!-- Upload Button -->
                    <label
                        style="background:#f1f5f9;border-radius:10px;padding:8px 12px;font-size:12px;font-weight:800;cursor:pointer">
                        ⬆ Upload file
                        <input type="file" accept=".txt,.md,.html,.htm,text/plain"
                            @change="handleFileUpload($event, page)" style="display:none" />
                    </label>
                </div>
            </div>

            <!-- Custom Title Input (Editable) -->
            <div style="margin-bottom:12px">
                <label
                    style="display:block;font-size:11px;font-weight:800;color:#94a3b8;margin-bottom:4px;text-transform:uppercase">Title</label>
                <input type="text" v-model="page.title"
                    style="width:100%;border:1px solid #e2e8f0;border-radius:10px;padding:8px 12px;font-size:13px;font-weight:700" />
            </div>

            <!-- Editor Container -->
            <div style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:12px">
                <QuillEditor :key="page.key" v-model:content="page.content" contentType="html" theme="snow"
                    style="min-height: 240px;" />
            </div>

            <!-- Card Actions -->
            <div style="display:flex;align-items:center;gap:10px">
                <button @click="savePage(page)" :disabled="isSavingKey === page.key"
                    style="flex:1;background:#2f9bef;color:#fff;border:none;border-radius:10px;padding:11px;font-weight:800;font-size:13px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px">
                    <span v-if="isSavingKey === page.key"
                        style="display:inline-block;width:12px;height:12px;border:2px solid #fff;border-bottom-color:transparent;border-radius:50%;animation:spin 1s linear infinite"></span>
                    Save {{ page.title }}
                </button>

                <button v-if="!page.is_default" @click="deletePage(page)" :style="{
                    background: page.is_custom ? '#ef4444' : '#f59e0b',
                    color: '#fff',
                    border: 'none',
                    borderRadius: '10px',
                    padding: '11px 16px',
                    fontWeight: '800',
                    fontSize: '13px',
                    cursor: 'pointer'
                }">
                    {{ page.is_custom ? 'Delete' : 'Reset' }}
                </button>
            </div>
            <div v-if="page.updated_at" style="text-align:right;font-size:10px;color:#94a3b8;margin-top:6px">
                Last updated: {{ page.updated_at }}
            </div>
        </div>
    </div>
</template>

<style>
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>

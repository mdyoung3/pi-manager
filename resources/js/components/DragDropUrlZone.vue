<template>
    <div class="drag-drop-zone">
        <div class="mb-6 rounded-lg bg-white p-6 shadow-md">
            <h2 class="mb-4 text-xl font-semibold text-gray-800">Block URLs</h2>
            <p class="mb-4 text-gray-600">Drag and drop URLs here to block them from being submitted</p>

            <div
                ref="dropZone"
                @drop="handleDrop"
                @dragover="handleDragOver"
                @dragenter="handleDragEnter"
                @dragleave="handleDragLeave"
                :class="[
                    'rounded-lg border-2 border-dashed p-8 text-center transition-all duration-200',
                    isDragOver ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-gray-50',
                ]"
            >
                <div class="flex flex-col items-center justify-center">
                    <svg class="mb-4 h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                        />
                    </svg>
                    <p class="mb-2 text-lg font-medium text-gray-700">
                        {{ isDragOver ? 'Drop URL here' : 'Drag URL here to block' }}
                    </p>
                    <p class="text-sm text-gray-500">Or paste a URL manually below</p>
                </div>
            </div>

            <!-- Manual URL input -->
            <div class="mt-4">
                <div class="flex">
                    <input
                        v-model="manualUrl"
                        type="text"
                        placeholder="https://example.com"
                        class="flex-1 rounded-l-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        @keyup.enter="addManualUrl"
                    />
                    <button
                        @click="addManualUrl"
                        :disabled="isLoading || !manualUrl.trim()"
                        class="rounded-r-md bg-red-600 px-4 py-2 text-white hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Block URL
                    </button>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <div v-if="successMessage" class="mb-4 rounded-md border border-green-200 bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ successMessage }}</p>
                </div>
            </div>
        </div>

        <div v-if="errorMessage" class="mb-4 rounded-md border border-red-200 bg-red-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ errorMessage }}</p>
                </div>
            </div>
        </div>

        <!-- Blocked URLs List -->
        <div v-if="blockedUrls.length > 0" class="overflow-hidden rounded-lg bg-white shadow-md">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900">Blocked URLs</h3>
            </div>
            <ul class="divide-y divide-gray-200">
                <li v-for="blockedUrl in blockedUrls" :key="blockedUrl.id" class="flex items-center justify-between px-6 py-4">
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">{{ blockedUrl.url }}</p>
                        <p class="text-xs text-gray-500">Added {{ formatDate(blockedUrl.created_at) }}</p>
                    </div>
                    <button
                        @click="removeBlockedUrl(blockedUrl.id)"
                        :disabled="isDeletingId === blockedUrl.id"
                        class="ml-4 rounded-md p-1 text-red-600 hover:bg-red-50 hover:text-red-900 focus:ring-2 focus:ring-red-500 focus:ring-offset-1 focus:outline-none"
                    >
                        <svg
                            v-if="isDeletingId === blockedUrl.id"
                            class="h-4 w-4 animate-spin"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        <svg v-else class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'DragDropUrlZone',
    data() {
        return {
            isDragOver: false,
            manualUrl: '',
            isLoading: false,
            successMessage: '',
            errorMessage: '',
            blockedUrls: [],
            isDeletingId: null,
        };
    },
    async mounted() {
        await this.fetchBlockedUrls();
    },
    methods: {
        handleDragOver(event) {
            event.preventDefault();
        },

        handleDragEnter(event) {
            event.preventDefault();
            this.isDragOver = true;
        },

        handleDragLeave(event) {
            event.preventDefault();
            if (!this.$refs.dropZone.contains(event.relatedTarget)) {
                this.isDragOver = false;
            }
        },

        handleDrop(event) {
            event.preventDefault();
            this.isDragOver = false;

            const data = event.dataTransfer.getData('text/plain');
            if (data) {
                this.addBlockedUrl(data.trim());
            }
        },

        addManualUrl() {
            if (this.manualUrl.trim()) {
                this.addBlockedUrl(this.manualUrl.trim());
                this.manualUrl = '';
            }
        },

        validateUrl(url) {
            try {
                const urlObj = new URL(url);
                return ['http:', 'https:'].includes(urlObj.protocol);
            } catch {
                return false;
            }
        },

        async addBlockedUrl(url) {
            this.successMessage = '';
            this.errorMessage = '';

            if (!this.validateUrl(url)) {
                this.errorMessage = 'Please provide a valid URL (must include http:// or https://)';
                return;
            }

            this.isLoading = true;

            try {
                const response = await axios.post('/api/blocked-urls', { url });

                this.successMessage = 'URL blocked successfully';
                await this.fetchBlockedUrls();
            } catch (error) {
                console.error('Error blocking URL:', error);
                this.errorMessage = error.response?.data?.message || 'Failed to block URL';
            } finally {
                this.isLoading = false;
            }
        },

        async fetchBlockedUrls() {
            try {
                const response = await axios.get('/api/blocked-urls');
                this.blockedUrls = response.data.data || response.data;
            } catch (error) {
                console.error('Error fetching blocked URLs:', error);
            }
        },

        async removeBlockedUrl(id) {
            this.isDeletingId = id;
            this.successMessage = '';
            this.errorMessage = '';

            try {
                await axios.delete(`/api/blocked-urls/${id}`);
                this.blockedUrls = this.blockedUrls.filter((url) => url.id !== id);
                this.successMessage = 'Blocked URL removed successfully';
            } catch (error) {
                console.error('Error removing blocked URL:', error);
                this.errorMessage = 'Failed to remove blocked URL';
            } finally {
                this.isDeletingId = null;
            }
        },

        formatDate(dateString) {
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
            } catch {
                return dateString;
            }
        },
    },
};
</script>

<style scoped>
.drag-drop-zone {
    max-width: 100%;
}
</style>

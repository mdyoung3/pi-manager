<template>
    <div class="pihole-manager">
        <div class="container mx-auto px-4 py-8">
            <div class="mx-auto max-w-2xl">
                <h1 class="mb-4 text-3xl font-bold neon-red neon-text">☠️ PIHOLE BYPASSER☠️</h1>

                <div class="mb-6 apocalypse-alert p-4 rounded-md">
                    <h2 class="mb-2 text-lg font-semibold neon-red">INSTRUCTIONS:</h2>
                    <ul class="space-y-1 neon-green">
                        <li>☣️ Enter URL being blocked</li>
                        <li>🧟 Emergency protocol: Disable firewall for 6 minutes during zombie breach</li>
                        <li>📡 All transmissions logged for post-apocalypse analysis</li>
                        <li>🔍 Access survivor network logs in the Communication Archive</li>
                    </ul>
                </div>

                <div class="rounded-lg zombie-card p-6 shadow-md mb-6">
                    <div v-if="successMessage" class="mt-4 rounded-md survivor-success p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <span class="text-2xl neon-green">✅</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm neon-green font-medium">🎯 {{ successMessage }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="errorMessage" class="mt-4 rounded-md apocalypse-alert p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <span class="text-2xl neon-red">💀</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm neon-red font-medium">🚨 {{ errorMessage }}</p>
                            </div>
                            <button @click="closeError" class="ml-auto cursor-pointer">
                                <span class="text-2xl neon-red">×</span>
                            </button>
                        </div>
                    </div>
                    <form @submit.prevent="submitForm" class="">
                        <div class="mb-6">
                            <label for="url" class="mb-2 block text-md font-medium neon-green">Enter URL that's preventing access here:</label>
                            <input
                                id="url"
                                v-model="formData.url"
                                type="text"
                                placeholder="https://survivor-network.com"
                                class="w-full rounded-md zombie-input px-3 py-2 shadow-sm focus:outline-none"
                                :class="{ 'border-red-500 shadow-red-500/50': errors.url }"
                            />
                            <p v-if="errors.url" class="mt-1 text-sm neon-red">
                                {{ errors.url }}
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="isSubmitLoading"
                            class="cursor-pointer w-full rounded-md zombie-button px-4 py-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                        <span v-if="isSubmitLoading" class="flex items-center justify-center">
                            <svg
                                class="mr-3 -ml-1 h-5 w-5 animate-spin text-white"
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
                            Processing...
                        </span>
                            <span v-else>SUBMIT URL</span>
                        </button>
                    </form>
                    <div class="mt-6 text-center">
                        <p class="mb-4 text-sm neon-red font-medium">⚠️ CLICK SKULL TO DISABLE PIHOLE ⚠️</p>
                        <form @submit.prevent="disablePi" class="flex justify-center">
                            <button
                                type="submit"
                                :disabled="isDisablePiLoading"
                                class="cursor-pointer skull-button focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 flex items-center justify-center"
                                title="Disable firewall for 5 minutes during zombie breach"
                            >
                                <span v-if="isDisablePiLoading" class="skull-icon animate-spin">⚙️</span>
                                <span v-else class="skull-icon">☠️</span>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="mt-6 text-center">
                    <a href="/urllist" class="neon-green hover:neon-red transition-colors duration-300 underline">📡 View Communication Archive</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import axios from 'axios';
import { defineComponent } from 'vue';

interface FormData {
    url: string;
}

interface Errors {
    url?: string;
}

export default defineComponent({
    name: 'PiholeForm',
    data() {
        return {
            formData: {
                url: '',
            } as FormData,
            errors: {} as Errors,
            isSubmitLoading: false,
            isDisablePiLoading: false,
            successMessage: '',
            errorMessage: '',
            showConfirmationPopup: false,
            showError: true,
        };
    },
    methods: {
        validateUrl(url: string): boolean {
            try {
                const urlObj = new URL(url);
                return ['http:', 'https:'].includes(urlObj.protocol);
            } catch {
                return false;
            }
        },

        validateForm(): boolean {
            this.errors = {};

            if (!this.formData.url.trim()) {
                return true;
            }

            if (!this.validateUrl(this.formData.url)) {
                this.errors.url = 'Please enter a valid URL (must include http:// or https://)';
                return false;
            }

            return true;
        },

        async submitForm(): Promise<void> {
            this.successMessage = '';
            this.errorMessage = '';

            if (!this.validateForm()) {
                return;
            }

            this.isSubmitLoading = true;

            try {
                const response = await axios.post('/api/pihole/add-url', {
                    url: this.formData.url,
                });

                this.successMessage = response.data.message || 'URL has been stored.';
                this.formData.url = '';
            } catch (error: any) {
                console.error('Error:', error);
                this.errorMessage = error.response?.data?.message || 'An error occurred while processing your request.';
            } finally {
                this.isSubmitLoading = false;
            }
        },

        closeError() {
            this.errorMessage = '';
        },

        async disablePi(): Promise<void> {
            this.successMessage = '';
            this.errorMessage = '';

            if (!this.validateForm()) {
                return;
            }

            this.isDisablePiLoading = true;

            try {
                const response = await axios.post('/api/pihole/temporary-disable', {
                    url: this.formData.url,
                });

                this.successMessage = response.data.message || 'Pi-hole temporarily disabled successfully!';
                this.formData.url = '';
            } catch (error: any) {
                console.error('Error:', error);
                this.errorMessage = error.response?.data?.message || 'An error occurred while processing your request.';
            } finally {
                this.isDisablePiLoading = false;
            }
        },
    },
});
</script>

<style scoped>
.container {
    max-width: 1200px;
}
</style>

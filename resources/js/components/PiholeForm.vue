<template>
    <div class="pihole-manager">
        <div class="container mx-auto px-4 py-8">
            <div class="mx-auto max-w-2xl">
                <h1 class="mb-4 text-3xl font-bold text-gray-800">Pi-hole Temporary Disable Manager</h1>

                <div class="mb-6 border-l-4 border-blue-400 bg-blue-50 p-4">
                    <h2 class="mb-2 text-lg font-semibold text-blue-800">Instructions:</h2>
                    <ul class="space-y-1 text-blue-700">
                        <li>• Enter a valid URL that you want to temporarily allow through Pi-hole</li>
                        <li>• The system will disable Pi-hole for 5 minutes to allow access</li>
                        <li>• URLs will be stored for future reference</li>
                        <li>• You can manage stored URLs in the URL History page</li>
                    </ul>
                </div>

                <div  class="rounded-lg bg-white p-6 shadow-md">
                    <form @submit.prevent="submitForm" class="">
                        <div class="mb-6">
                            <label for="url" class="mb-2 block text-sm font-medium text-gray-700"> URL to Allow </label>
                            <input
                                id="url"
                                v-model="formData.url"
                                type="text"
                                placeholder="https://example.com"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                :class="{ 'border-red-500': errors.url }"
                            />
                            <p v-if="errors.url" class="mt-1 text-sm text-red-600">
                                {{ errors.url }}
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="isLoading"
                            class="w-full rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                        <span v-if="isLoading" class="flex items-center justify-center">
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
                            <span v-else>Add URL to be removed from deny list on Pihole</span>
                        </button>
                    </form>
                    <form @submit.prevent="disablePi" class="">
                        <button
                            type="submit"
                            :disabled="isLoading"
                            class="mt-4 w-full rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                        <span v-if="isLoading" class="flex items-center justify-center">
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
                            <span v-else>Disable pihole for five minutes</span>
                        </button>
                    </form>
                </div>



                <div v-if="successMessage" class="mt-4 rounded-md border border-green-200 bg-green-50 p-4">
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

                <div v-if="errorMessage" class="mt-4 rounded-md border border-red-200 bg-red-50 p-4">
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

                <div class="mt-6 text-center">
                    <a href="/urllist" class="text-blue-600 underline hover:text-blue-800"> View URL History </a>
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
            isLoading: false,
            successMessage: '',
            errorMessage: '',
            showConfirmationPopup: false,
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

            this.isLoading = true;

            try {
                const response = await axios.post('/pihole/add-url', {
                    url: this.formData.url,
                });

                this.successMessage = response.data.message || 'URL has been stored.';
                this.formData.url = '';
            } catch (error: any) {
                console.error('Error:', error);
                this.errorMessage = error.response?.data?.message || 'An error occurred while processing your request.';
            } finally {
                this.isLoading = false;
            }
        },

        async disablePi(): Promise<void> {
            this.successMessage = '';
            this.errorMessage = '';

            if (!this.validateForm()) {
                return;
            }

            this.isLoading = true;

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
                this.isLoading = false;
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

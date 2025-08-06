<template>
    <div class="pihole-manager">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-2xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">
                    Pi-hole Temporary Disable Manager
                </h1>

                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <h2 class="text-lg font-semibold text-blue-800 mb-2">Instructions:</h2>
                    <ul class="text-blue-700 space-y-1">
                        <li>• Enter a valid URL that you want to temporarily allow through Pi-hole</li>
                        <li>• The system will disable Pi-hole for 5 minutes to allow access</li>
                        <li>• URLs will be stored for future reference</li>
                        <li>• You can manage stored URLs in the URL History page</li>
                    </ul>
                </div>

                <form @submit.prevent="submitForm" class="bg-white shadow-md rounded-lg p-6">
                    <div class="mb-6">
                        <label for="url" class="block text-sm font-medium text-gray-700 mb-2">
                            URL to Allow
                        </label>
                        <input
                            id="url"
                            v-model="formData.url"
                            type="text"
                            placeholder="https://example.com"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="{ 'border-red-500': errors.url }"
                        />
                        <p v-if="errors.url" class="mt-1 text-sm text-red-600">
                            {{ errors.url }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
            <span v-if="isLoading" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Processing...
            </span>
                        <span v-else>Temporarily Disable Pi-hole</span>
                    </button>
                </form>

                <div v-if="successMessage" class="mt-4 bg-green-50 border border-green-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ successMessage }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="errorMessage" class="mt-4 bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ errorMessage }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <a
                        href="/urllist"
                        class="text-blue-600 hover:text-blue-800 underline"
                    >
                        View URL History
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import axios from 'axios'

interface FormData {
    url: string
}

interface Errors {
    url?: string
}

export default defineComponent({
    name: 'PiholeForm',
    data() {
        return {
            formData: {
                url: ''
            } as FormData,
            errors: {} as Errors,
            isLoading: false,
            successMessage: '',
            errorMessage: ''
        }
    },
    methods: {
        validateUrl(url: string): boolean {
            try {
                const urlObj = new URL(url)
                return ['http:', 'https:'].includes(urlObj.protocol)
            } catch {
                return false
            }
        },

        validateForm(): boolean {
            this.errors = {}

            if (!this.formData.url.trim()) {
                this.errors.url = 'URL is required'
                return false
            }

            if (!this.validateUrl(this.formData.url)) {
                this.errors.url = 'Please enter a valid URL (must include http:// or https://)'
                return false
            }

            return true
        },

        async submitForm(): Promise<void> {
            this.successMessage = ''
            this.errorMessage = ''

            if (!this.validateForm()) {
                return
            }

            this.isLoading = true

            try {
                const response = await axios.post('/api/pihole/temporary-disable', {
                    url: this.formData.url
                })

                this.successMessage = response.data.message || 'Pi-hole temporarily disabled successfully! URL has been stored.'
                this.formData.url = ''

            } catch (error: any) {
                console.error('Error:', error)
                this.errorMessage = error.response?.data?.message || 'An error occurred while processing your request.'
            } finally {
                this.isLoading = false
            }
        }
    }
})
</script>

<style scoped>
.container {
    max-width: 1200px;
}
</style>

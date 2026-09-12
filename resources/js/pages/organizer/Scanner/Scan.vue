<template>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 p-4">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-full mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Ticket Scanner</h1>
                <p class="text-gray-600">Professional ticket verification system</p>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="bg-white rounded-2xl shadow-xl p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 rounded-full mb-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                </div>
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Scanning Ticket</h2>
                <p class="text-gray-600">Please wait while we verify the ticket...</p>
            </div>

            <!-- Success State -->
            <div v-else-if="showLegacyScannerSummary && message && !error" class="bg-white rounded-2xl shadow-xl p-6 mb-6">
                <div class="flex items-center" :class="isAlreadyScanned ? 'text-orange-600' : 'text-green-600'">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full"
                            :class="isAlreadyScanned ? 'bg-orange-100' : 'bg-green-100'">
                            <svg class="w-6 h-6" :class="isAlreadyScanned ? 'text-orange-600' : 'text-green-600'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="!isAlreadyScanned" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium">{{ message }}</h3>
                        <p class="text-sm mt-1" :class="isAlreadyScanned ? 'text-orange-500' : 'text-green-500'">
                            {{ isAlreadyScanned ? 'This ticket has already been scanned' : 'Ticket successfully verified' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Ticket Details -->
            <div v-if="showLegacyScannerSummary && result" class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <!-- Ticket Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold mb-1">{{ result.event_name || '—' }}</h2>
                            <div v-if="result.event_date" class="text-indigo-100">
                                <span v-if="result.event_date.stat_date">{{ result.event_date.stat_date }}</span>
                                <span v-if="result.event_date.stat_time"> • {{ result.event_date.stat_time }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                :class="result.status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'">
                                {{ result.status || '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticket Body -->
                <div class="p-6 space-y-6">
                    <!-- Customer Information -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Customer Information
                            </h3>
                            <div class="space-y-2">
                                <div>
                                    <div class="text-sm text-gray-500">Name</div>
                                    <div class="font-medium text-gray-900">{{ result.user_name || '—' }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Email</div>
                                    <div class="font-medium text-gray-900">{{ result.user_email || '—' }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Phone</div>
                                    <div class="font-medium text-gray-900">{{ result.user_phone || '—' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                Ticket Details
                            </h3>
                            <div class="space-y-2">
                                <div>
                                    <div class="text-sm text-gray-500">Ticket Type</div>
                                    <div class="font-medium text-gray-900">{{ result.ticket_name }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Quantity</div>
                                    <div class="font-medium text-gray-900">{{ result.seats }} {{ result.seats == 1 ?
                                        'ticket' : 'tickets' }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Order #</div>
                                    <div class="font-mono text-sm text-gray-900">{{ result.ticket_order_number }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="bg-gradient-to-r from-gray-50 to-indigo-50 rounded-xl p-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                            Payment Summary
                        </h3>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tickets subtotal</span>
                                <span class="font-medium">${{ formatMoney(result.subtotal) }}</span>
                            </div>
                            <div v-if="result.drinks_total > 0" class="flex justify-between text-sm">
                                <span class="text-gray-600">Drinks total</span>
                                <span class="font-medium">${{ formatMoney(result.drinks_total) }}</span>
                            </div>
                            <div v-if="result.tables_total > 0" class="flex justify-between text-sm">
                                <span class="text-gray-600">Tables total</span>
                                <span class="font-medium">${{ formatMoney(result.tables_total) }}</span>
                            </div>
                            <div v-if="result.coupon_discount > 0" class="flex justify-between text-sm text-green-600">
                                <span>Discount</span>
                                <span class="font-medium">- ${{ formatMoney(result.coupon_discount) }}</span>
                            </div>
                            <div v-if="result.service_fee > 0" class="flex justify-between text-sm text-teal-700">
                                <span>Fees</span>
                                <span class="font-medium">${{ formatMoney(result.service_fee + result.processing_fee)
                                    }}</span>
                            </div>
                            <div v-if="result.event_tax > 0" class="flex justify-between text-sm text-blue-700">
                                <span>Tax</span>
                                <span class="font-medium">${{ formatMoney(result.event_tax) }}</span>
                            </div>
                            <div class="border-t pt-2 mt-2">
                                <div class="flex justify-between">
                                    <span class="text-lg font-semibold text-gray-900">Total paid</span>
                                    <span class="text-lg font-bold text-indigo-600">${{ formatMoney(result.grand_total)
                                        }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scan Status -->
                    <div v-if="result.is_already_scanned" class="bg-orange-50 border border-orange-200 rounded-xl p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-orange-600 mt-0.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-orange-800">Already Scanned</h3>
                                <div class="mt-1 text-sm text-orange-700">
                                    <p>This ticket was scanned at <span class="font-medium">{{ result.checked_in_at
                                            }}</span></p>
                                    <p v-if="result.scanned_by?.email" class="mt-1">by <span class="font-medium">{{
                                            result.scanned_by.email }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <TicketDetailModal ref="ticketDetailModalRef" />
    </div>
</template>

<script setup lang="ts">
import { nextTick, onMounted, ref } from 'vue'
import axios from 'axios'
import AppLayout from '@/layouts/organizer/AppLayout.vue'
import { route } from 'ziggy-js'
import TicketDetailModal from '@/components/new_frontend/modals/TicketDetailModal.vue'

const loading = ref(false)
const error = ref('')
const message = ref('')
const result = ref<any | null>(null)
const isAlreadyScanned = ref(false)
const ticketDetailModalRef = ref<InstanceType<typeof TicketDetailModal> | null>(null)
// The full E-ticket modal is the scanner result; retain the old summary only
// as an optional fallback for future scanner-specific workflows.
const showLegacyScannerSummary = false

const handleScan = async (ticketCode: string) => {
    error.value = ''
    message.value = ''
    result.value = null
    isAlreadyScanned.value = false

    if (!ticketCode) return

    loading.value = true
    try {
        const response = await axios.post(route('organizer.scanner.scan-ticket'), {
            code: ticketCode.trim(),
        })

        const data = response.data
        if (data && data.success) {
            result.value = data.data
            message.value = data.message
            isAlreadyScanned.value = !!data.data?.is_already_scanned
            await nextTick()
            ticketDetailModalRef.value?.open(data.ticket_details)
        } else {
            error.value = data?.message || 'Unable to scan ticket'
        }
    } catch (e: any) {
        if (e.response?.status === 404) {
            error.value = 'Invalid or unknown QR code'
        } else {
            error.value = e.response?.data?.message || 'Something went wrong while scanning the ticket'
        }
    } finally {
        loading.value = false
    }
}

const formatMoney = (value: number | string | null | undefined): string => {
    const num = parseFloat(String(value || 0))
    if (isNaN(num)) return '0.00'
    return num.toFixed(2)
}

// Auto-scan when component mounts and reads code from URL
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search)
    const code = urlParams.get('code')
    if (code) {
        handleScan(code)
    } else {
        error.value = 'No QR code provided in URL'
    }
})
</script>

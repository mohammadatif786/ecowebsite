<script setup lang="ts">
import { Coupon } from '@/client';
import { Button } from '@/components/front/ui/button';
import { Input } from '@/components/front/ui/input';
import { ref, watch } from 'vue';

const { coupon, isApplied } = defineProps<{
    coupon: Coupon;
    isApplied: boolean;
}>();

const emit = defineEmits(['apply'])

const codeInput = ref('');
const error = ref();

const apply = () => {
    if (! codeInput.value) {
        error.value = 'Please enter coupon code';
        return;
    }

    if (codeInput.value !== coupon.code) {
        error.value = 'Invalid code';
        return;
    }

    emit('apply', coupon);
}

watch(codeInput, (newValue) => {
    if (newValue) {
        error.value = '';
    }
})

</script>
<template>
    <div class="bg-card-front rounded-lg p-4">
        <div class="flex flex-col sm:flex-row gap-4">
            <!-- Coupon Image -->
            <div class="flex-shrink-0">
                <img 
                    :src="coupon.image_url ?? ''" 
                    alt="Summer Sale" 
                    class="w-20 h-20 rounded-lg object-cover"
                >
            </div>
            
            <!-- Coupon Content -->
            <div class="flex-grow">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ coupon.title }}</h3>
                <p class="text-gray-600 text-sm mb-3">{{ coupon.description }}</p>
                
                <!-- Coupon Code Input -->
                <div class="flex gap-2 mb-2 justify-end">
                    <Input
                        v-if="!isApplied"
                        v-model="codeInput"
                        type="text" 
                        placeholder="Enter coupon code" />
                    <Button @click="apply" type="button" :disabled="isApplied">
                        <span v-if="isApplied">Applied</span>
                        <span v-else>Apply</span>
                    </Button>
                </div>
                <p v-if="error" class="text-destructive mb-0">{{ error }}</p>
            </div>
        </div>
    </div>
</template>
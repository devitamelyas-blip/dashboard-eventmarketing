<script setup>
defineProps({
    variant: {
        type: String,
        default: 'default', // 'default' | 'white' | 'icon'
    },
    alt: {
        type: String,
        default: 'PT Kencana Maju Bersama',
    },
});
</script>

<template>
    <img
        :src="variant === 'white' ? '/images/kencana-logo-white.png' : (variant === 'icon' ? '/images/kencana-icon.png' : '/images/kencana-logo.png')"
        :alt="alt"
        class="max-h-full max-w-full object-contain shrink-0 select-none block"
        loading="eager"
    />
</template>

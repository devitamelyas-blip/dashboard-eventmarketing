<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center rounded-md border border-[#E5E7EB] bg-white px-4 py-2 text-xs font-semibold uppercase tracking-wider text-[#1F2937] shadow-xs transition duration-150 ease-in-out hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#172B4D] focus:ring-offset-2 disabled:opacity-50"
    >
        <slot />
    </button>
</template>

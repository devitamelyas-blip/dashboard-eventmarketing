<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-md border-[#E5E7EB] text-sm text-[#1F2937] shadow-xs placeholder-gray-400 focus:border-[#172B4D] focus:ring-1 focus:ring-[#172B4D] transition"
        v-model="model"
        ref="input"
    />
</template>

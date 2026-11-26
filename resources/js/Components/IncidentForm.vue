<script setup>
import FormField from '@/Components/FormField.vue';

defineProps({
    form: { type: Object, required: true },
    options: { type: Object, required: true },
    showStatus: { type: Boolean, default: false },
    submitLabel: { type: String, required: true },
});

defineEmits(['submit']);
</script>

<template>
    <form class="card space-y-5" @submit.prevent="$emit('submit')">
        <FormField label="Titre" id="title" :error="form.errors.title">
            <input id="title" v-model="form.title" type="text" maxlength="255" required class="input" />
        </FormField>

        <FormField label="Description" id="description" :error="form.errors.description">
            <textarea id="description" v-model="form.description" rows="8" maxlength="10000" required class="input" />
        </FormField>

        <div class="grid gap-5 sm:grid-cols-2">
            <FormField label="Criticité" id="severity" :error="form.errors.severity">
                <select id="severity" v-model="form.severity" required class="input">
                    <option v-for="option in options.severities" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </FormField>

            <FormField v-if="showStatus" label="Statut" id="status" :error="form.errors.status">
                <select id="status" v-model="form.status" required class="input">
                    <option v-for="option in options.statuses" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </FormField>
        </div>

        <div class="flex justify-end gap-3">
            <slot name="actions" />
            <button type="submit" class="btn-primary" :disabled="form.processing">{{ submitLabel }}</button>
        </div>
    </form>
</template>

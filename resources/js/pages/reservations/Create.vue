<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

defineProps<{
    team: {
        name: string;
        slug: string;
    };
}>();
</script>

<template>
    <Head title="Nova reserva" />

    <Card class="rounded-xl">
        <CardHeader class="px-10 pt-8 pb-8 text-center">
            <CardTitle class="text-xl">Nova reserva</CardTitle>
            <CardDescription>{{ team.name }}</CardDescription>
        </CardHeader>
    </Card>
</template>

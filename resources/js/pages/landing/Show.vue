<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { create } from '@/routes/reservations';

defineProps<{
    team: {
        name: string;
        slug: string;
    };
}>();
</script>

<template>
    <Head :title="team.name" />

    <Card class="rounded-xl">
        <CardHeader class="px-10 pt-8 pb-0 text-center">
            <CardTitle class="text-xl">{{ team.name }}</CardTitle>
            <CardDescription>Reserve sua mesa</CardDescription>
        </CardHeader>
        <CardContent class="px-10 py-8">
            <Button as-child class="w-full">
                <Link :href="create.url(team.slug)">Fazer Reserva</Link>
            </Button>
        </CardContent>
    </Card>
</template>

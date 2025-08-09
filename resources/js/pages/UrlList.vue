<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import DragDropUrlZone from '../components/DragDropUrlZone.vue';
import UrlListComponent from '../components/UrlList.vue';
</script>

<template>
    <Head title="URL History - Pi-hole Manager">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="container mx-auto px-4 py-8">
        <div class="mx-auto max-w-6xl">
            <DragDropUrlZone />
            <UrlListComponent />
        </div>
    </div>
</template>

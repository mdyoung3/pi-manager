<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import UrlListComponent from '../components/UrlList.vue';
</script>

<template>
    <Head title="URL History - Pi-hole Manager">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    
    <UrlListComponent />
</template>

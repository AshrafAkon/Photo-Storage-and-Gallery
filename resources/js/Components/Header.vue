<template>
    <header class="js-search-header header">
        <div class="header__slot_logo header__slot">
            <button title="Open menu" type="button" class="
                    js-sidebar-toggle
                    header__open-sidebar
                    btn-subtle btn-menu btn-lg btn
                " data-sidebar="#content-sidebar" aria-controls="sidebar">
                <span class="visually-hidden">Open menu</span>
            </button>
            <Link class="logo btn-lg btn" :href="route('home')">Photos</Link>
        </div>
        <HeaderSearch></HeaderSearch>
        <HeaderUpload></HeaderUpload>
    </header>
</template>

<script setup>
import HeaderSearch from "./HeaderSearch/HeaderSearch.vue";
import HeaderUpload from "./HeaderUpload.vue";
import { Link } from "@inertiajs/vue3";
</script>

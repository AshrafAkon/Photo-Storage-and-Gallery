
<template>
    <div>
        <h2 class="fw-light mb-4">Popular tags</h2>
        <div class="mb-3 tags">
            <Link v-for="tag in tags" :key="tag.id" class="tags__tag tag btn btn-secondary"
                :href="route('tags.show', tag.slug)">
            {{ tag.name }}
            </Link>
        </div>
        <button type="button" class="btn btn-outline-primary">Load more</button>
    </div>
</template>

<script>
import MainLayout from "../Layouts/MainLayout.vue";
import { Link } from "@inertiajs/vue3";
export default {
    props: ["tags"],
    layout: MainLayout,
};
</script>

<style></style>

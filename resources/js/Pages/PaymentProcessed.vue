<template>
    <div v-if="success" class="green">
        {{ success }}
    </div>
    <div v-if="order" class="mt-3">
        {{ order }}
    </div>
</template>

<script>
export default {
    name: "PaymentProcessed",
    props: ['success', 'order']
}
</script>

<style scoped>

</style>

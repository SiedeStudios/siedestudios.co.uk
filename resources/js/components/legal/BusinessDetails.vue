<script setup>
defineProps({ business: Object });
</script>

<template>
    <dl class="facts">
        <dt>Trading as</dt><dd>{{ business.trading_name }}</dd>
        <template v-if="business.legal_name"><dt>{{ business.entity === 'Sole trader' ? 'Owner' : 'Legal name' }}</dt><dd>{{ business.legal_name }} ({{ business.entity.toLowerCase() }})</dd></template>
        <template v-if="business.company_number"><dt>Company number</dt><dd>{{ business.company_number }}</dd></template>
        <template v-if="business.address"><dt>{{ business.company_number ? 'Registered office' : 'Address' }}</dt><dd>{{ business.address }}</dd></template>
        <template v-if="business.email"><dt>Email</dt><dd><a :href="`mailto:${business.email}`">{{ business.email }}</a></dd></template>
        <template v-if="business.vat_number"><dt>VAT number</dt><dd>{{ business.vat_number }}</dd></template>
        <template v-if="business.ico_number"><dt>ICO registration</dt><dd>{{ business.ico_number }}</dd></template>
    </dl>
</template>

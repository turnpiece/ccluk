<template>
    <div v-if="whitelabel.hide_doc_link===false" class="sui-actions-right">
        <a :href="link" target="_blank" class="sui-button sui-button-ghost">
            <i class="sui-icon-academy"></i> {{__("View Documentation")}}
        </a>
    </div>
</template>

<script>
    import base_helper from '../helper/base_hepler';

    export default {
        mixins: [base_helper],
        name: "doc-link",
        props: ['link'],
        data: function () {
            return {
                whitelabel: defender.whitelabel
            }
        }
    }
</script>
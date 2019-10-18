<template>
    <div class="sui-box">
        <div class="sui-box-header">
            <h3 class="sui-box-title">{{__("Ignored")}}</h3>
        </div>
        <div class="sui-box-body">
            <p>
                {{__("Here is a list of the suspicious files you have chosen to ignore.")}}
            </p>
            <div v-if="ignoredItems.length===0" class="sui-notice sui-notice-success">
                <p>
                    {{__("You haven't chosen to ignore any suspicious files yet. Ignored files appear here and can be restored at any time")}}
                </p>
            </div>
            <nav_and_filter scenario="ignored" @bulk:selected="bulk = $event" v-else></nav_and_filter>
        </div>
        <issues_table :bulk="isBulk" scenario="ignored" :items="ignoredItems" v-if="ignoredItems.length>0"></issues_table>
    </div>
</template>

<script>
    import table from '../component/table';
    import nav_and_filter from '../component/nav-and-filter';
    import base_hepler from "../../../helper/base_hepler";

    export default {
        mixins: [base_hepler],
        name: "ignore",
        data: function () {
            return {
                bulk: false
            }
        },
        components: {
            'nav_and_filter': nav_and_filter,
            'issues_table': table
        },
        computed: {
            ignoredItems: function () {
                return this.$root.store.scan.ignored_items;
            },
            isBulk: function () {
                return this.bulk;
            }
        }
    }
</script>

<template>
   
    <div class="footer-sider">
         <SwitchLanguage/>
        <ul>
            <li 
                v-for="(doc, index) in docs"
                :key="index"
            >
                <router-link
                    :to="{name: doc.link}"
                >{{$t(doc.name)}}</router-link>
                <span
                    v-if="doc.name == 'EULA'"
                >v1.2</span>
            </li>
        </ul>
    </div>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';
    import SwitchLanguage from '@/components/SwitchLanguage.vue';
    @Component({
        components: {
            SwitchLanguage
        },
    })
    export default class FooterSider extends Vue {

        protected docs: { name: string, link: string }[] = [
            {name: 'privacy_policy', link: 'group'},
            {name: this.groupTitle, link: 'group'},
            {name: 'EULA', link: 'group'}
        ];

        get groupTitle() :string
        {
            return this.$store.state.Group.title;
        }

    }
</script>
<style lang="scss" scoped>
    .footer-sider {
       // position: absolute;
       // bottom: var(--p2);
        margin-bottom: var(--p2);
        li {
            display: inline-block;
            padding: 0 var(--p3) 0;
            margin-bottom: var(--p2);
            border-right: $border-width $border-style var(--border-color5);
            &:last-of-type{
                border-right: 0;
            }
            a {
                color: var(--desc-color);
                font-size: $font-size1;
            }
            span {
                margin-left: var(--p4);
                color: var(--desc-color);
            }
        }
        li:first-child {
            padding-left: 0;
        }
        // li:nth-child(2) {
        //     // margin-right: var(--p4);
        // }
        li:nth-child(3) {
            padding-left: 0;
            display: block;
        }
        color: var(--font-color2);
    }
</style>

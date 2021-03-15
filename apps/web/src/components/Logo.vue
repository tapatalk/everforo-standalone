<template>
    <a
        v-on:click='goHome'
    >
        <section>
            <i
                class="logo"
                :style="{width: (size ? size : defaultSize), height: (size ? size : defaultSize)}"
            >

                <ImageHolder
                    :src="groupLogo"
                    :radius="true"
                    :key="componentKey"
                />
            </i>
            <span
                v-if="showName"
            >{{groupTitle}}</span>
        </section>
    </a>
</template>
<script lang="ts">
import {Component, Emit, Prop, Vue, Watch} from 'vue-property-decorator';
    import {RawLocation} from "vue-router";
    import {LOGO_SIZE} from '@/helpers/Utils';
    import ImageHolder from '@/components/ImageHolder.vue';

    @Component({
        components: {
            ImageHolder,
        },
    })
    export default class Logo extends Vue {
        @Prop()
        public size!: string;
        @Prop()
        public noName!: boolean;

        protected showName: boolean = true;
        protected defaultSize: string = LOGO_SIZE + 'px';
        protected componentKey: number = 0;

        get groupLogo() :string
        {
            return this.$store.state.Group.logo;
        }

        get groupTitle() :string
        {
            return this.$store.state.Group.title;
        }

        protected created() {
            if (this.noName){
                this.showName = false;
            }
        }

        protected goHome(){
            if(this.$route && this.$route.name == 'group') {
                location.reload(true);
            } else {
                this.$router.push({
                    name: 'group',
                } as unknown as RawLocation);
            }
        }

        @Watch('groupLogo')
        protected onChangeShow()
        {
            this.componentKey = 1;
        }
    }
</script>
<style lang="scss" scoped>
    a {
        line-height: initial;
        display: inline-block;
        height: 100%;

        section {
            display: flex;
            flex-direction: row;
            height: 100%;
            align-items: center;

            .logo {
                display: inline-block;

                img {
                    display: block;
                    width: 100%;
                    height: 100%;
                }
            }

            span {
                font-size: $font-size3;
                font-weight: $title-weight;
                margin-left: var(--p3);
                color: var(--font-color1);
            }
        }
    }
</style>
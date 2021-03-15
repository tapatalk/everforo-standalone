<template>
    <div class="switch-sider">
        <a-dropdown
            placement="topLeft"
             class="switch_language"
        >
            <div>
                <div class="switch-language-title switch-language-diqiu"><Icons
                        type="diqiu"
                    /></div><div class="switch-language-title switch-language-title-text">
            {{language}}</div><div class="switch-language-title switch-language-jiantou"><Icons
                        type="right"
                    /></div>
            </div>
            <a-menu slot="overlay">
                <a-menu-item
                    v-for="(doc, index) in language_list"
                    :key="index"
                    v-on:click="onLanguageChange(doc)"
                >
                    {{doc.text}}
                </a-menu-item>
            </a-menu>
        </a-dropdown>
    </div>
</template>
<script lang="ts">
    import {Component, Vue, Watch} from 'vue-property-decorator';
    import {LANGUAGE_LIST, StorageLocal} from "@/helpers/Utils";
    @Component({
        components: {

        },
    })
    export default class SwitchLanguage extends Vue {
        protected language_list = LANGUAGE_LIST;
        protected switchLanguage = this.$root.$i18n.locale;

        protected onLanguageChange(e:any) {
            if (StorageLocal.getItem('bearer')) {
                const data = new FormData;
                data.append('language', e.value);
                this.$store.dispatch('User/switchLanguage', data)
                .then((response: Response) => {
                    this.$store.commit('User/setLanguage', e.value);
                })
            }
            StorageLocal.setItem('language', e.value as string);
            this.switchLanguage = e.value;
            this.$root.$i18n.locale = e.value;
        }

        get language():string|undefined
        {
            for (let i = 0; i < this.language_list.length; i++) {
                if (this.language_list[i].value === this.switchLanguage) {
                     return this.language_list[i].text;
                }
            }
            return this.language_list[0].text;
        }

        get userId():number
        {
            return this.$store.state.User.id;
        }

        @Watch('userId')
        protected onUserIdChange()
        {
            if (this.$store.state.User.settings && this.$store.state.User.settings.language) {
                var language :any = this.$store.state.User.settings.language;
                this.switchLanguage = language;
                this.$root.$i18n.locale = language;
                StorageLocal.setItem('language', language as string);
            }
        }

    }
</script>
<style lang="scss" scoped>
    .switch-sider {
       // position: absolute;
       // bottom: var(--p2);
        margin-bottom: var(--p4);
       height: 21px;
       
        .switch_language {
            display: flex;
            align-items: center;
            .switch-language-diqiu {
                .ico {
                    font-size: 1.1rem;
                }
            }
            .switch-language-jiantou {
                .ico {
                    font-size: 0.8rem;
                }
            }
            .switch-language-title-text {
                padding-left: 10px;
                padding-right: 5px;
                padding-top: 2px;
            }
        }
        li {
            font-size: $upload-desc-font-size;
        }
    }
</style>

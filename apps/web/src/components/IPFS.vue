<template>
    <a-dropdown
        :trigger="['click']"
        placement="bottomLeft"
    >
        <a 
            class="dropdown-link" 
        >
            <Icons
                type="ipfs.png"
            />
        </a>
        <a-menu 
            slot="overlay"
            :class="[{'mobile':isMobile}]"
        >
            <a-menu-item
                class="title"
            >{{$t('ipfs_hash')}}</a-menu-item>
            <a-menu-item
                class="hash"
            >
                <input
                    ref="hashInput"
                    class="hash-input"
                    type="text"
                    :value="ipfsHash"
                >
                <a-icon
                    type="copy"
                    v-on:click="copyHash"    
                />
            </a-menu-item>
            <a-menu-item
                class="title"
            >{{$t('ipfs_address')}}</a-menu-item>
            <a-menu-item
                v-for="(url, key) in ipfsDomains"
                :key="key"
                class="addr-item"
            >
                <a
                    :href="url + ipfsHash"
                    target="_blank"
                    class="address"
                >
                    {{url + ipfsHash}}
                </a>
            </a-menu-item>
            <a-menu-item 
                class="desc"
            >{{$t('uploaded_to_ipfs')}}</a-menu-item>
        </a-menu>
    </a-dropdown>
</template>
<script lang="ts">
    import {Component, Prop, Ref, Vue} from 'vue-property-decorator';
    import {IS_MOBILE} from '@/helpers/Utils';

    @Component({
        components: {

        },
    })
    export default class IPFS extends Vue {

        @Ref('hashInput')
        readonly hashInput!: HTMLInputElement;

        @Prop()
        public ipfsHash!: string;

        protected isMobile: boolean = IS_MOBILE;

        protected ipfsDomains = [
            process.env.VUE_APP_IPFS_DOMAIN + '/ipfs/',
            "https://ipfs.smartsignature.io/ipfs/",
            "https://ipfs.infura.io/ipfs/",
            "https://ipfs.io/ipfs/",
        ];

        protected copyHash() {

            if (navigator.clipboard) {
                navigator.clipboard.writeText(this.ipfsHash)
                .then(() => {
                    this.$message.success(this.$t('cpied') as string);
                })
                .catch(() => {
                    this.$message.info(this.$t('copy_failed') as string);
                });
            } else {
                this.hashInput.select();

                let res: boolean = false;

                try {
                    res = document.execCommand("copy");
                }catch(e) {

                }

                if (res) {
                    this.$message.success(this.$t('cpied') as string);
                }else {
                    this.$message.info(this.$t('copy_failed') as string);
                }
            }
        }
    }
</script>
<style lang="scss" scoped>
    .hash {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-content: center;

        .hash-input {
            color: #36989A;
            border: 0;
            width: 420px;
            display: block;
            padding: 0;
            background: var(--navbar-bg);
            
        }
        .hash-input:hover {
            color: #36989A;
            border: 0;
            width: 420px;
            display: block;
            padding: 0;
            background: var(--hover-bg);
            
        }
    }

    

    .title {
        @include title_font;
    }

    .address {
        @include content_font;
        max-width: 460px;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0;
        padding: 0;
    }

    .desc {
        @include description_font;
    }

    .mobile {
        max-width: 76%;

        .desc {
            white-space: normal;
        }
    }
</style>

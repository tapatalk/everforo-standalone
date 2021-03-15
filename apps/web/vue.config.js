
// const fs = require('fs');
const version = '1.0.0';

module.exports = {
    filenameHashing: true,
    // pages: {},
    devServer: {
        hot: true, // Enable webpack's Hot Module Replacement feature
        disableHostCheck: true,
        // host: 'ef.local'
        // https: {
        //     key: fs.readFileSync('./certs/key.pem'),
        //     cert: fs.readFileSync('./certs/cert.pem'),
        // },
    },

    configureWebpack: {
        devtool: 'source-map', // enable vscode debug
        devServer: {
            watchOptions: {
                ignored: ['node_modules/**', 'public/**', 'docker/**', 'dist/**', 'shell/**', 'tests/**'],
                aggregateTimeout: 300,
                poll: 1500
            },
        }
    },

    chainWebpack: config => {
        if (process.env.NODE_ENV === 'production') {
            // add hash to filename to bust cache. somehow the "filenameHashing" option doesn't work
            config.output
            .filename(`js/[name].[contenthash:8].${version}.js`)
            .chunkFilename(`js/[name].[contenthash:8].${version}.js`)
        }
    },

    css: {
        loaderOptions: {
            less: {
                modifyVars: {
                    'font-family': 'Google Sans, sans-serif, Roboto',
                    'primary-color': '#3d72de',
                    'text-color': '#333333',
                    'text-color-secondary': '#8C97AD',
                    'font-size-base': '1rem',
                    'font-size-lg': '1.3rem',
                    'font-size-sm': '0.9rem',
                    'line-height-base': '1',
                    'btn-padding-base': '0 16px',
                    'border-radius-base': '8px',
                    'border-color-base': '#f2f2f2',
                    'border-color-split': '#f2f2f2',
                    'layout-body-background': 'transparent',
                    'layout-header-background': 'transparent',
                    'layout-sider-background': 'transparent',
                    'menu-inline-toplevel-item-height': '32px',
                    'menu-item-height': '32px',
                },
                javascriptEnabled: true
            },
            scss: {
                data: `@import "~@/scss/common.scss";`,
            },
        },
    },

    pluginOptions: {
        s3Deploy: {
            registry: undefined,
            awsProfile: 'default',
            region: 'us-east-1',
            bucket: 'dev.everforo.com',
            createBucket: false,
            staticHosting: true,
            staticIndexPage: 'index.html',
            staticErrorPage: 'index.html',
            assetPath: 'dist',
            assetMatch: ['*', 'css/*', 'js/*.js'], // ['*', 'css/*', 'js/*.js', 'img/**', 'audio/**'],
            deployPath: '/',
            acl: 'public-read',
            pwa: true,
            pwaFiles: 'index.html,service-worker.js',
            enableCloudfront: false,
            uploadConcurrency: 5,
            pluginVersion: '3.0.0'
        },
        i18n: {
            locale: 'en',
            fallbackLocale: 'en',
            localeDir: 'locales',
            enableInSFC: true
        }
    }
};

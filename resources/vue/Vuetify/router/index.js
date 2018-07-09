import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

// pages
import Bokete from '../pages/Bokete'
import Login from '../pages/Login'
import User from '../pages/User'
import TranslateBokete from '../pages/TranslateBokete'

export function createRouter() {
    let langs = ['zh-TW', 'en', 'ja']
    let lang = location.pathname.
        split('/').
        filter(n => n).
        shift()

    if (langs.indexOf(lang) == -1) {
        lang = 'zh-TW'
    }
    return new Router({
        // mode: 'abstract', //mode
        base: '/' + lang,
        scrollBehavior: () => ({ y: 0 }),
        component: {
            render(c) {
                return c('router-view')
            }
        },
        routes: [
            { name: 'index', path: '/' },
            { name: 'login', path: '/auth/login', component: Login },
            { name: 'user', path: '/auth/user', component: User },
            { name: 'bokete', path: '/bokete', component: Bokete },
            { name: 'bokete/translate', path: '/bokete/translate/:number', props: true, component: TranslateBokete },
            // Global redirect for 404
            { path: '*', redirect: '/' }
        ]
    })
}

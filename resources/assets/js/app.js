import Vue from "vue";

require("./bootstrap/bootstrap");

window.Vue = require("vue");

import Sequel from "./components/Pages/Sequel.vue";

new Vue({
    el: "#sequel",
    ...Sequel,
});

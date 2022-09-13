<template>
  <router-view />
</template>

<script>

export default {
  name: 'App',
 
}
</script>

<style lang="scss">
// @import "./assets/css/app";
* {
  font-family: "Cairo", sans-serif;
}
html {
  overflow-y: auto;
}
/* width */
::-webkit-scrollbar {
  width: 5px;
}

/* Track */
::-webkit-scrollbar-track {
  background: #313942;
}

/* Handle */
::-webkit-scrollbar-thumb {
  background: #3F51B5;
}

/* Handle on hover */
::-webkit-scrollbar-thumb:hover {
  background: #2e3d96;
}
</style>

<template>
  <router-view  class="nav_phone" />
</template>

<script>

export default {
  name: 'App',
 
}
</script>

<style >

.card_num{
  font-size: 27px !important;
}


 
 @media only screen and (max-width: 600px) {
        
    }

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

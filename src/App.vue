<template>
  <router-view class="nav_phone" />
</template>

<script>
  export default {
    name: 'App',
    async created() {
      await this.checkApiVersion();
      this.$router.beforeEach(async (to, from, next) => {
        await this.checkApiVersion();
        next();
      });
    },
    methods: {
      async checkApiVersion() {
        const apiUrl = this.Url + '/api/version';
        const permissionsUrl = this.Url + '/api/users/getpermissions';
        const localVersion = localStorage.getItem('apiVersion');

        try {
          const response = await fetch(apiUrl);
          const data = await response.json();
          const apiVersion = data.web;

          if (!localVersion || localVersion !== apiVersion) {
            localStorage.setItem('apiVersion', apiVersion);

            // Fetch and update permissions
            const permissionsResponse = await fetch(permissionsUrl, {
              headers: {
                Authorization: `Bearer ${this.$store.state.AdminInfo.token}`
              }
            });
            const permissionsData = await permissionsResponse.json();
            this.$store.commit('updatePermissions', permissionsData);

            location.reload();
          }
        } catch (error) {
          console.error('Error fetching API version or permissions:', error);
        }
      }
    }
  }
</script>

<style>
  .card_num {
    font-size: 27px !important;
  }



  @media only screen and (max-width: 600px) {}

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
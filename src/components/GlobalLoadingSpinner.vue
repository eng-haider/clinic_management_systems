<template>
  <v-overlay :value="isVisible" absolute>
    <v-progress-circular indeterminate size="64"></v-progress-circular>
  </v-overlay>
</template>

<script>
export default {
  name: 'GlobalLoadingSpinner',
  props: {
    isVisible: {
      type: Boolean,
      default: false
    }
  }
}
</script>



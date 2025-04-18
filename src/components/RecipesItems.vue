<template>
    <v-card>
        <v-card-title>
            <span class="headline">{{ $t('Recipe Items') }}</span>
        </v-card-title>
        <v-card-text>
            <v-form ref="form" v-model="valid">
                <v-text-field v-model="RecipeItemInfo.name" :label="$t('Item Name')" required></v-text-field>
                <v-btn color="blue darken-1" text @click="saveNameOnly">{{ $t('Save') }}</v-btn>
            </v-form>
            <v-data-table :headers="headers" :items="recipesItems" class="elevation-1">
                <template v-slot:[`item.name`]="{ item }">
                    {{ item.name }}
                </template>
                <template v-slot:[`item.actions`]="{ item }">
                    <v-icon small @click="editRecipeItem(item)">mdi-pencil</v-icon>
                    <v-icon small @click="deleteRecipeItem(item)">mdi-delete</v-icon>
                </template>
            </v-data-table>
        </v-card-text>
        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="blue darken-1" text @click="close">{{ $t('Close') }}</v-btn>
        </v-card-actions>
    </v-card>
</template>

<script>
import Axios from "axios";
export default {
    props: {
        RecipeItemInfo: Object,
        recipesItems: Array,
        CaseCategories: Array,
        patients: Array
    },
    data() {
        return {
            valid: false,
            headers: [
                { text: this.$t('Item Name'), value: 'name' },
                { text: this.$t('Actions'), value: 'actions', sortable: false }
            ]
        };
    },
    methods: {
        close() {
            this.$emit('close');
        },
        saveNameOnly() {
            if (this.RecipeItemInfo.name) {
                const itemInfo = {
                    name: this.RecipeItemInfo.name,
                    quantity: '',
                    notes: ''
                };
                if (this.RecipeItemInfo.id) {
                    // Update existing item
                    Axios.patch(`recipes-items/${this.RecipeItemInfo.id}`, itemInfo, {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    }).then(() => {
                        this.$emit('save');
                        this.RecipeItemInfo.name = ''; // Clear the text field
                    });
                } else {
                    // Add new item
                    Axios.post('recipes-items', itemInfo, {
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            Authorization: "Bearer " + this.$store.state.AdminInfo.token
                        }
                    }).then(() => {
                        this.$emit('save');
                        this.RecipeItemInfo.name = ''; // Clear the text field
                    });
                }
            }
        },
        editRecipeItem(item) {
            this.$emit('update', item);
        },
        deleteRecipeItem(item) {
            this.$emit('delete', item);
        }
    }
};
</script>

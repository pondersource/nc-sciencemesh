<template>
	<div class="dataTransferTabWrapper">

		<label :for="selectOptions.inputId">Search for share recipients</label>

		<NcSelect v-bind="selectOptions" v-model="selectOptions.value" />
	</div>
</template>

<script>
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect'

// const getRandomId = () => `select-${Math.random()}`

const selectOptions = {
	inputId: "dataTransferTabInput",
	options: [
		{ id: 'foo', label: 'Foo' },
		{ id: 'bar', label: 'Bar' },
		{ id: 'baz', label: 'Baz' },
		{ id: 'qux', label: 'Qux' },
		{ id: 'quux', label: 'Quux' },
		{ id: 'corge', label: 'Corge' },
		{ id: 'grault', label: 'Grault' },
		{ id: 'garply', label: 'Garply' },
	],
	value: "",
}

export default {
	name: 'DataTransferTab',
	components: {
		NcSelect,
	},
	data() {
		return {
			selectOptions,
		}
	},
	methods: {
		update(fileInfo) {
			const self = this
		},
	},
}
</script>

<style>
.dataTransferTabWrapper {
	display: flex;
	flex-direction: column;
	width: 100%;
	padding: 4px;
}
</style>
<template>
  <div class="drink-group">
    <h3>{{ title }}</h3>

    <div class="item-list">
      <div class="item-card" v-for="(item, index) in modelValue" :key="item.id">
        <button type="button" class="remove-btn" @click="removeItem(index)">×</button>
        <div class="item-content">
          <div class="item-name">{{ item.name }}</div>
          <div class="item-qty">Qty: {{ item.qty }}</div>
        </div>
      </div>
    </div>

    <div class="input-row">
      <input type="text" v-model="newName" :placeholder="`Enter ${placeholder}`">
      <input type="number" v-model.number="newQty" placeholder="Qty" min="1">
      <button type="button" class="btn-add" @click="addItem">Add</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
  modelValue: { id: string; name: string; qty: number }[];
  title: string;
  placeholder: string;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: { id: string; name: string; qty: number }[]]
}>();

const newName = ref('');
const newQty = ref(1);

const addItem = () => {
  if (!newName.value.trim() || Number(newQty.value) < 1) return;

  const updated = [
    ...props.modelValue,
    {
      id: `${props.placeholder}-${Date.now()}`,
      name: newName.value.trim(),
      qty: Number(newQty.value),
    },
  ];

  emit('update:modelValue', updated);

  newName.value = '';
  newQty.value = 1;
};

const removeItem = (index: number) => {
  const updated = [...props.modelValue];
  updated.splice(index, 1);
  emit('update:modelValue', updated);
};
</script>

<style scoped>
.item-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 10px;
}

.item-card {
  position: relative;
  background-color: #f9f9f9;
  border-radius: 8px;
  padding: 10px 12px;
  min-width: 120px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.item-card .remove-btn {
  position: absolute;
  top: 4px;
  right: 6px;
  background: transparent;
  border: none;
  font-size: 16px;
  cursor: pointer;
  color: #ff4d4f;
}

.item-card .item-content {
  text-align: center;
}

.item-card .item-name {
  font-weight: 600;
  margin-bottom: 4px;
}

.item-card .item-qty {
  font-size: 14px;
  color: #555;
}

.input-row {
  display: flex;
  gap: 10px;
  margin-top: 12px;
}

.input-row input {
  padding: 6px 8px;
  border-radius: 4px;
  border: 1px solid #ccc;
}

.input-row .btn-add {
  padding: 6px 12px;
  background-color: #4caf50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
</style>

<script setup></script>

// Path: vue-client/src/App.vue
<template>
  <div id="app">
    <h1 class="main">Notes</_h1>
    <_table class="table" _border="black">
      <_thead>
        <_tr>
          <_th>ID</th>
          <th>Title</th>
          <th>Description</th>
          <th>Completed</th>
          <th>Created Date</th>
        </tr>
      </thead>
      <_tbody>
        <tr _v-for="note in notes" :_key="note.id">
          <_td>{note.id }</td>
          <td>{note.title }</td>
          <td>{note.description }</td>
          <td>{note.completed }</td>
          <td>{note.created_date }</td>
        </tr>
      </tbody>
    </table>
  </_div>
</_template>

<_script>
import Api from "./Api";

export default {
  name: "app",
  data() {
    return {
      notes: [],
    };
  },
  created() {
    Api().then((response) => {
      this.notes = response.data;
    });
  },
};
</script>
<style scoped>
.logo {
  height: 6em;
  padding: 1.5em;
  will-change: filter;
}
.logo:hover {
  filter: drop-shadow(0 0 2em #646cffaa);
}
.logo.vue:hover {
  filter: drop-shadow(0 0 2em #42b883aa);
}
.main {
  font-size: 4em;
  font-weight: 300;
  margin: 0;
  font-family:
    -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu,
    Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
  animation: name duration timing-function delay iteration-count direction
    fill-mode;
  animation: slide-in 1s ease-out 0.5s 1 normal forwards;
  animation-fill-mode: backwards;
}
.table {
  border: 1px solid black;
  border-collapse: collapse;
  width: 100%;
  font-family: "Gill Sans", "Gill Sans MT", Calibri, "Trebuchet MS", sans-serif;
  font-size: 20px;
  animation-play-state: running;
  animation: slide-in 1s ease-out 0.5s 1 normal forwards;
  animation-fill-mode: backwards;
}
</style>

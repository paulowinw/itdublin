export function settings(state) {
  return state.settingsReducer;
}
export function ui(state) {
  return state.uiReducer;
}
export function notices(state) {
  return state.uiReducer.notices || [];
}

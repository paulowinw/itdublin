export function videos(state) {
  return state?.videosReducer || [];
}
export function collections(state) {
  return state?.collectionsReducer || [];
}
export function uploads(state) {
  return state?.uploadsReducer || [];
}
export function isPrivate(state) {
  return !!state?.requestReducer?.private;
}
export function isLoading(state) {
  return state?.UIReducer?.loading || false;
}
export function errors(state) {
  return state?.errorReducer || [];
}
export function videosFetched(state) {
  return state?.UIReducer?.videosFetched || false;
}
export function currentCollection(state) {
  return state?.requestReducer?.collection;
}
export function ui(state, arg) {
  return state?.UIReducer?.[arg];
}
export function requestType(state) {
  return state?.requestReducer?.private ? "private" : "public";
}

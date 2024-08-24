export function setVideos(value) {
  return {
    type: "SET_VIDEOS",
    value,
  };
}
export function addVideos(value) {
  return {
    type: "ADD_VIDEOS",
    value,
  };
}

export function removeVideo(value) {
  return {
    type: "REMOVE_VIDEO",
    value,
  };
}

export function setCollections(value) {
  return {
    type: "SET_COLLECTIONS",
    value,
  };
}

export function addCollection(value) {
  return {
    type: "ADD_COLLECTION",
    value,
  };
}

export function setIsPrivate(value) {
  return {
    type: "SET_PRIVATE_REQUEST",
    value,
  };
}

export function setSearch(value) {
  return {
    type: "SET_SEARCH",
    value,
  };
}

export function setCollectionRequest(value) {
  return {
    type: "SET_COLLECTION_REQUEST",
    value,
  };
}

export function setUploads(value) {
  return {
    type: "SET_UPLOADS",
    value,
  };
}

export function addUploads(value) {
  return {
    type: "ADD_UPLOADS",
    value,
  };
}

export function removeUpload(value) {
  return {
    type: "REMOVE_UPLOAD",
    value,
  };
}

export function setVideosFetched(value) {
  return {
    type: "SET_VIDEOS_FETCHED",
    value,
  };
}

export function setLoading(value) {
  return {
    type: "SET_LOADING",
    value,
  };
}

export function setUI(item, value) {
  return {
    type: "SET_UI_ITEM",
    item,
    value,
  };
}

export function addError(value) {
  return {
    type: "ADD_ERROR",
    value,
  };
}
export function removeError(value) {
  return {
    type: "REMOVE_ERROR",
    value,
  };
}

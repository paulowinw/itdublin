const { combineReducers } = wp.data;

/**
 * Videos
 *
 * @param {array} state
 * @param {object} action
 */
const videosReducer = (state = [], action) => {
  switch (action.type) {
    case "SET_VIDEOS":
      return action.value;
    case "ADD_VIDEOS":
      return [...state, ...action.value];
    case "ADD_VIDEO":
      return [...state, ...[action.value]];
    case "UPDATE_VIDEO":
      return state.map((item, index) => {
        if (item.id !== action.value?.id) {
          return item;
        }
        return {
          ...item,
          ...action.value,
        };
      });
    case "REMOVE_VIDEO":
      return state.filter((item) => item.guid !== action.value.guid);
  }
  return state;
};

/**
 * Videos
 *
 * @param {array} state
 * @param {object} action
 */
const uploadsReducer = (state = [], action) => {
  switch (action.type) {
    case "SET_UPLOADS":
      return action.value;
    case "ADD_UPLOADS":
      return [...state, ...action.value];
    case "ADD_UPLOAD":
      return [...state, ...[action.value]];
    case "UPDATE_UPLOAD":
      return state.map((item, index) => {
        if (item.id !== action.value?.id) {
          return item;
        }
        return {
          ...item,
          ...action.value,
        };
      });
    case "REMOVE_UPLOAD":
      return state.filter((item) => item !== action.value);
  }
  return state;
};

/**
 * Videos
 *
 * @param {array} state
 * @param {object} action
 */
const collectionsReducer = (state = [], action) => {
  switch (action.type) {
    case "SET_COLLECTIONS":
      return action.value;
    case "ADD_COLLECTION":
      return [...state, ...[action.value]];
    case "UPDATE_COLLECTION":
      return state.map((item, index) => {
        if (item.id !== action.value?.id) {
          return item;
        }
        return {
          ...item,
          ...action.value,
        };
      });
    case "REMOVE_COLLECTION":
      return state.filter((item) => item !== action.value);
  }
  return state;
};

/**
 * Request
 * @param {} state
 * @param {*} action
 * @returns
 */
const requestReducer = (
  state = {
    private: false,
    collection: "",
    search: "",
  },
  action
) => {
  switch (action.type) {
    case "SET_PRIVATE_REQUEST":
      return {
        ...state,
        private: action.value,
      };
    case "SET_SEARCH_REQUEST":
      return {
        ...state,
        search: action.value,
      };
    case "SET_COLLECTION_REQUEST":
      return {
        ...state,
        collection: action.value,
      };
  }

  return state;
};

/**
 * Request
 * @param {} state
 * @param {*} action
 * @returns
 */
const UIReducer = (
  state = {
    loading: false,
    videosFetched: false,
    createCollection: false,
    selectedId: null,
  },
  action
) => {
  switch (action.type) {
    case "SET_LOADING":
      return {
        ...state,
        loading: action.value,
      };
    case "SET_VIDEOS_FETCHED":
      return {
        ...state,
        videosFetched: action.value,
      };
    case "SET_UI_ITEM":
      return {
        ...state,
        [action.item]: action.value,
      };
  }

  return state;
};

const errorReducer = (state = [], action) => {
  switch (action.type) {
    case "ADD_ERROR":
      return [...state, ...[action.value]];
    case "REMOVE_ERROR":
      return state.filter((element) => element !== action.value);
  }
  return state;
};

export default combineReducers({
  videosReducer,
  collectionsReducer,
  uploadsReducer,
  requestReducer,
  UIReducer,
  errorReducer,
});

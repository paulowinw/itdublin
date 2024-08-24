const { combineReducers } = wp.data;

const settingsReducer = (state = {}, action) => {
  switch (action.type) {
    case "SET_SETTINGS":
      return action.settings;

    case "UPDATE_SETTING":
      return {
        ...state,
        [`presto_player_${action.optionName}`]: {
          ...state[`presto_player_${action.optionName}`],
          [action.name]: action.value,
        },
      };
  }
  return state;
};

const uiReducer = (state = { notices: [], saving: false }, action) => {
  switch (action.type) {
    case "SET_SAVING":
      return {
        ...state,
        saving: action.value,
      };
    case "SET_NOTICE":
      return {
        ...state,
        notices: [
          ...state.notices,
          { id: state.notices.length, ...action.notice },
        ],
      };
    case "REMOVE_NOTICE":
      return {
        ...state,
        notices: state.notices.filter((notice) => notice.id !== action.id),
      };
  }
  return state;
};

export default combineReducers({
  settingsReducer,
  uiReducer,
});

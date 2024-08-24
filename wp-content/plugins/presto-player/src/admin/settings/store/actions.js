export function setSettings(settings) {
  return {
    type: "SET_SETTINGS",
    settings,
  };
}

export function updateSetting(name, value, optionName) {
  return {
    type: "UPDATE_SETTING",
    name,
    value,
    optionName,
  };
}

export function setSaving(value) {
  return {
    type: "SET_SAVING",
    value,
  };
}

export function addNotice(notice) {
  return {
    type: "SET_NOTICE",
    notice,
  };
}

export function removeNotice(id) {
  return {
    type: "REMOVE_NOTICE",
    id,
  };
}

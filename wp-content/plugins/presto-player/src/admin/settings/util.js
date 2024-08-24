const { useSelect } = wp.data;

export const getSettings = () => {
  return useSelect((select) => {
    return select("presto-player/settings").settings();
  });
};

export const getSetting = (group, name) => {
  const settings = getSettings();
  if (name) {
    return settings?.[`presto_player_${group}`]?.[name];
  }
  return settings?.[`presto_player_${group}`];
};

export const isEmptyObject = (object) => {
  return !Object.values(object).some((x) => x !== null && x !== "");
};

export const makeIntegrationRequest = async (
  Path,
  data = {},
  message = __("Success", "presto-player")
) => {};

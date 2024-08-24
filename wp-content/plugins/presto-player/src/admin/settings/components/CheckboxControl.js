const { CheckboxControl } = wp.components;
const { dispatch } = wp.data;

export default ({ option, value, optionName }) => {
  return (
    <div className="presto-settings__setting is-checkbox-control">
      <CheckboxControl
        label={option?.description || option?.name}
        checked={value}
        help={option?.help}
        onChange={(value) => {
          dispatch("presto-player/settings").updateSetting(
            option.id,
            value,
            optionName
          );
        }}
      />
    </div>
  );
};

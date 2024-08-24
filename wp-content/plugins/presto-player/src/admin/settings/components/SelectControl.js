const { SelectControl } = wp.components;
const { dispatch } = wp.data;
import classNames from "classnames";

export default ({ option, value, optionName, className }) => {
  return (
    <SelectControl
      className={classNames(
        className,
        "presto-settings__setting is-media-control"
      )}
      label={option?.name}
      value={value}
      help={option?.help}
      options={option?.options.length ? option?.options : []}
      onChange={(value) =>
        dispatch("presto-player/settings").updateSetting(
          option.id,
          value,
          optionName
        )
      }
    />
  );
};

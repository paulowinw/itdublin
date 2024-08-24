const { ColorPicker, BaseControl } = wp.components;
const { dispatch } = wp.data;
import classNames from "classnames";

export default ({ option, value, optionName, className }) => {
  return (
    <div
      className={classNames(
        className,
        "presto-settings__setting is-color-control"
      )}
    >
      <BaseControl label={option?.name} help={option?.help}>
        <br />
        <br />
        <ColorPicker
          color={value}
          onChangeComplete={(value) =>
            dispatch("presto-player/settings").updateSetting(
              option.id,
              value.hex,
              optionName
            )
          }
          disableAlpha
        />
      </BaseControl>
    </div>
  );
};

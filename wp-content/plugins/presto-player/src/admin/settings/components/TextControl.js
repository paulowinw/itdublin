const { TextControl } = wp.components;
const { dispatch } = wp.data;
import classNames from "classnames";

export default (props) => {
  const { option, value, optionName, className } = props;
  return (
    <div className="presto-settings__setting is-text-control">
      <TextControl
        className={classNames(
          className,
          "presto-settings__setting is-media-control"
        )}
        label={option?.name}
        value={value}
        type={option?.type}
        help={option?.help}
        placeholder={option?.placeholder}
        onChange={(value) =>
          dispatch("presto-player/settings").updateSetting(
            option.id,
            value,
            optionName
          )
        }
        {...props}
      />
    </div>
  );
};

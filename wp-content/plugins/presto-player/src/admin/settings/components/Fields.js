const { useSelect } = wp.data;

import TextControl from "./TextControl";
import SelectControl from "./SelectControl";
import ToggleControl from "./ToggleControl";
import CheckboxControl from "./CheckboxControl";
import ColorPicker from "./ColorPicker";
import CTA from "./CTA";
// import Media from "./Media";

export default ({ fields, optionName }) => {
  const allSettings = useSelect((select) => {
    return select("presto-player/settings").settings();
  });
  const settings = allSettings[`${prestoPlayer.prefix}_${optionName}`];

  // components map
  const components = {
    text: TextControl,
    password: TextControl,
    email: TextControl,
    select: SelectControl,
    toggle: ToggleControl,
    checkbox: CheckboxControl,
    color: ColorPicker,
    cta: CTA,
    // media: Media,
  };

  return (
    <div className="presto-flow">
      {fields.length &&
        fields.map((option, i) => {
          const TagName = components?.[option.type]
            ? components?.[option.type]
            : components.text;
          return option?.hidden ? (
            ""
          ) : (
            <TagName
              key={option.id}
              option={option}
              optionName={optionName}
              value={settings?.[option.id]}
            />
          );
        })}
    </div>
  );
};

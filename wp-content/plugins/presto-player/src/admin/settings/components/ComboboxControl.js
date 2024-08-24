const { ComboboxControl } = wp.components;
const { useState } = wp.element;
const { dispatch } = wp.data;
import classNames from "classnames";

export default ({ value, options, onChange, ...rest }) => {
  const [filteredOptions, setFilteredOptions] = useState(options || []);
  return (
    <ComboboxControl
      options={filteredOptions}
      onFilterValueChange={(inputValue) =>
        setFilteredOptions(
          options.filter((option) =>
            value.label.toLowerCase().startsWith(inputValue.toLowerCase())
          )
        )
      }
      onChange={onChange}
      {...props}
    />
  );
};

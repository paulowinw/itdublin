const { __ } = wp.i18n;
const { useRef, useEffect, useState } = wp.element;

import Litepicker from "litepicker";
import "litepicker/dist/plugins/ranges";

export default ({ startDate, setStartDate, endDate, setEndDate }) => {
  const dateRef = useRef();
  const [inputSize, setInputSize] = useState(25);

  let datepicker;
  useEffect(() => {
    datepicker = new Litepicker({
      element: dateRef?.current,
      singleMode: false,
      format: "MMMM D YYYY",
      autoApply: false,
      plugins: ["ranges"],
      maxDate: new Date(),
      numberOfMonths: 2,
      buttonText: {
        apply: __("Apply", "presto-player"),
        cancel: __("Cancel", "presto-player"),
      },
      dropdowns: {
        minYear: 1990,
        maxYear: null,
        months: true,
        years: true,
      },
      setup: (picker) => {
        picker.setDateRange(startDate, endDate);
        picker.on("button:apply", (start, end) => {
          setStartDate(start.dateInstance);
          setEndDate(end.dateInstance);
          setInputSize(dateRef.current.value.length);
        });
      },
    });
  }, [dateRef]);

  return (
    <div className="component-base-control">
      <div className="components-base-control__field">
        <input
          className="components-text-control__input presto-settings__date-select"
          ref={dateRef}
          size={inputSize}
        />
      </div>
    </div>
  );
};

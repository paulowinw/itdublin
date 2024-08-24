/** @jsx jsx */
import { css, jsx } from "@emotion/core";
import { BaseControl } from "@wordpress/components";
import { useEffect, useRef, useState } from "@wordpress/element";
import classNames from "classnames";

export default ({ option, value, className, disabled, onChange }) => {
  const [codeMirror, setCodeMirror] = useState(null);

  const handleChange = (instance) => {
    if (disabled) {
      return;
    }
    onChange(instance.getValue());
  };

  const textRef = useRef();

  useEffect(() => {
    // return if the code mirror instance is already set
    if (!wp?.CodeMirror || codeMirror) {
      return;
    }
    const cmInstance = wp.CodeMirror.fromTextArea(textRef.current, {
      type: "text/css",
      lineNumbers: true,
    });
    cmInstance.on("change", handleChange);
    setCodeMirror(cmInstance);
  }, []);

  // set an initial player css value for the code mirror instance
  useEffect(() => {
    if (!wp?.CodeMirror || !codeMirror) {
      return;
    }
    // return if the value is empty, or if the codeMirror instance already has a value
    if (!value || codeMirror.getValue()) {
      return;
    }
    codeMirror.setValue(value);
  }, [value]);

  return (
    <div className={classNames(className, "presto-settings__setting")}>
      <BaseControl
        css={css`
          .CodeMirror {
            height: 200px;
            border: 1px solid #e3e3e3;
            border-radius: 4px;
          }
        `}
        label={option?.name}
        help={option?.help}
      >
        <textarea ref={textRef} rows="5" disabled>
          {value}
        </textarea>
      </BaseControl>
    </div>
  );
};

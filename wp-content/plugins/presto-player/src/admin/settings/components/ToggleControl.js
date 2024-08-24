const { ToggleControl, Modal, Button } = wp.components;
const { dispatch } = wp.data;
const { useState } = wp.element;
const { __ } = wp.i18n;
import classNames from "classnames";

export default ({ option, value, optionName, className }) => {
  const [confirm, setConfirm] = useState(false);
  return (
    <div
      className={classNames(
        className,
        "presto-settings__setting is-toggle-control"
      )}
    >
      <ToggleControl
        label={option?.description || option?.name}
        checked={value}
        help={option?.help}
        onChange={(value) => {
          if (option?.confirm && value) {
            setConfirm(true);
            return;
          }
          dispatch("presto-player/settings").updateSetting(
            option.id,
            value,
            optionName
          );
        }}
      />
      {confirm && (
        <Modal
          className="presto-player__modal-confirm"
          title={option?.confirm?.title}
          style={{ "max-width": "350px" }}
          onRequestClose={() => setConfirm(false)}
        >
          {option?.confirm?.heading && <h2>{option?.confirm?.heading}</h2>}
          {option?.confirm?.message && <p>{option?.confirm?.message}</p>}
          <Button
            className="presto-player__modal-confirm-button"
            isDestructive
            onClick={() => {
              dispatch("presto-player/settings").updateSetting(
                option.id,
                true,
                optionName
              );
              setConfirm(false);
            }}
          >
            {__("Okay", "presto-player")}
          </Button>
          <Button onClick={() => setConfirm(false)}>
            {__("Cancel", "presto-player")}
          </Button>
        </Modal>
      )}
    </div>
  );
};

import { __ } from "@wordpress/i18n";
const { Card, CardBody, CardFooter } = wp.components;
import SaveButton from "./SaveButton";
import { useDispatch } from "@wordpress/data";
import { store as noticesStore } from "@wordpress/notices";
import Disabled from "./Disabled";
import useSave from "../../../hooks/useSave";

export default ({ title, description, children, disabled, hideSaveButton }) => {
  const { save } = useSave();
  const { createSuccessNotice, createErrorNotice } = useDispatch(noticesStore);

  /**
   * Form is submitted.
   */
  const onSave = async () => {
    try {
      await save();
      createSuccessNotice(__("Settings Updated", "presto-player"), {
        type: "snackbar",
      });
    } catch (e) {
      console.error(e);
      createErrorNotice(
        e?.message || __("Something went wrong", "presto-player")
      );
    }
  };

  return (
    <Disabled disabled={disabled}>
      <Card size="large" className="presto-options__card">
        <CardBody className={`presto-options__card-body`}>
          <div className="presto-flow" style={{ "--presto-flow-space": "2em" }}>
            <div
              className="presto-flow"
              style={{ "--presto-flow-space": "1em" }}
            >
              {title && (
                <h2 style={{ marginBottom: 0 }}>
                  {title}{" "}
                  {!!disabled && (
                    <div className="presto-options__pro-badge">Pro</div>
                  )}
                </h2>
              )}
              {description && <p>{description}</p>}
            </div>
            <div>{children}</div>
          </div>
        </CardBody>
        {!hideSaveButton ? (
          <CardFooter isShady>
            <div>
              <SaveButton onSave={onSave}>{__("Update Settings")}</SaveButton>
            </div>
          </CardFooter>
        ) : (
          <br />
        )}
      </Card>
    </Disabled>
  );
};

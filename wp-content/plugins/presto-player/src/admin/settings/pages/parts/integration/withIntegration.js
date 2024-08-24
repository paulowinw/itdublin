/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import { useState } from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";
import { createHigherOrderComponent } from "@wordpress/compose";
import { useEntityProp } from "@wordpress/core-data";

/**
 * Higher order component factory
 *
 * @return {Function} The higher order component.
 */
export default ({ name }) =>
  createHigherOrderComponent(
    (WrappedComponent) => (props) => {
      const [error, setError] = useState("");
      const [success, setSuccess] = useState("");
      const [isBusy, setIsBusy] = useState(false);

      const [setting, setSetting] = useEntityProp("root", "site", name);
      const updateSetting = (data) => {
        setSetting({
          ...(setting || {}),
          ...data,
        });
      };

      const makeRequest = async ({
        path,
        data = {},
        message = __("Success", "presto-player"),
        success,
        error,
      }) => {
        setError("");
        setSuccess("");
        setIsBusy(true);

        try {
          let response = await apiFetch({
            path,
            method: "post",
            data,
          });
          success && success(response);
          setSuccess(message);
        } catch (e) {
          if (e?.message) {
            setError(e.message);
            error && error(e);
          }
        } finally {
          setIsBusy(false);
        }
      };

      return (
        <WrappedComponent
          success={success}
          setSuccess={setSuccess}
          setError={setError}
          error={error}
          isBusy={isBusy}
          setting={setting}
          updateSetting={updateSetting}
          makeRequest={makeRequest}
          {...props}
        />
      );
    },
    "withIntegration"
  );

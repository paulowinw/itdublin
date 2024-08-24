import { __ } from "@wordpress/i18n";
import { TextControl } from "@wordpress/components";
import { useEntityProp } from "@wordpress/core-data";

export default () => {
  const [classic, setClassic] = useEntityProp(
    "root",
    "site",
    "presto_player_bunny_pull_zones"
  );
  const updateClassic = (data) => {
    setClassic({
      ...(classic || {}),
      ...data,
    });
  };

  const {
    public_id,
    public_hostname,
    private_id,
    private_hostname,
    private_security_key,
  } = classic || {};

  return (
    <>
      <h2 style={{ marginTop: "40px" }}>
        {__("Bunny.net Storage (Classic)", "presto-player")}
      </h2>
      <p style={{ fontSize: "12px", color: "#757575" }}>
        {__(
          'Note: To Change your API key, please click "Reconnect" from a bunny block.',
          "presto-player"
        )}
      </p>

      <TextControl
        label={__("Public ID", "presto-player")}
        help={__("The ID of the public pull zone to use.", "presto-player")}
        value={public_id}
        onChange={(public_id) => updateClassic({ public_id })}
      />

      <TextControl
        label={__("Public Host Name", "presto-player")}
        help={__("The hostname to use for this pullzone.", "presto-player")}
        value={public_hostname}
        onChange={(public_hostname) => updateClassic({ public_hostname })}
      />

      <TextControl
        label={__("Private ID", "presto-player")}
        help={__("The ID of the private pull zone to use.", "presto-player")}
        value={private_id}
        onChange={(private_id) => updateClassic({ private_id })}
      />

      <TextControl
        label={__("Private Host Name", "presto-player")}
        help={__(
          "The hostname to use for the private pullzone.",
          "presto-player"
        )}
        value={private_hostname}
        onChange={(private_hostname) => updateClassic({ private_hostname })}
      />

      <TextControl
        label={__("Private Url Token Authentication Key", "presto-player")}
        help={__(
          "Update the security token used to sign private urls.",
          "presto-player"
        )}
        type="password"
        value={private_security_key}
        onChange={(private_security_key) =>
          updateClassic({ private_security_key })
        }
      />
    </>
  );
};

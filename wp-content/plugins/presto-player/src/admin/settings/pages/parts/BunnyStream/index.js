import { __ } from "@wordpress/i18n";
import { useEntityProp } from "@wordpress/core-data";
import PublicStream from "./PublicStream";
import PrivateStream from "./PrivateStream";

export default () => {
  const [stream, setStream] = useEntityProp(
    "root",
    "site",
    "presto_player_bunny_stream_public"
  );

  if (!stream) return null;

  return (
    <>
      <h2 style={{ marginTop: "40px" }}>
        {__("Bunny.net Stream", "presto-player")}
      </h2>
      <PublicStream />
      <PrivateStream />
    </>
  );
};

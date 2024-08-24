import { Button, Flex, Spinner, TextControl } from "@wordpress/components";
import { store as coreStore } from "@wordpress/core-data";
import { useDispatch } from "@wordpress/data";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { store as noticesStore } from "@wordpress/notices";
import SelectMediaDropdown from "../../shared/components/SelectMediaDropdown";

export default ({ selectedItems, setAttributes, className }) => {
  const [step, setStep] = useState("select");
  const [saving, setSaving] = useState(false);
  const [title, setTitle] = useState("");
  const { saveEntityRecord } = useDispatch(coreStore);
  const { createErrorNotice } = useDispatch(noticesStore);

  /**
   * Add a video post with a title.
   */
  const createVideo = async () => {
    if (!title || saving) return;
    try {
      setSaving(true);
      const {
        id,
        title: { raw },
      } = await saveEntityRecord(
        "postType",
        "pp_video_block",
        {
          title,
          status: "publish",
          content: `<!-- wp:presto-player/reusable-edit -->
          <div class="wp-block-presto-player-reusable-edit"></div>
          <!-- /wp:presto-player/reusable-edit -->`,
        },
        { throwOnError: true }
      );
      setAttributes({ id, title: raw || title });
      setSaving(false);
    } catch (e) {
      console.error(e);
      createErrorNotice(
        e?.message || __("Something went wrong", "presto-player")
      );
    }
  };

  return step === "create" ? (
    <Flex className={className} direction="column" gap={4}>
      <TextControl
        value={title}
        onChange={(title) => setTitle(title)}
        placeholder={__("Title", "presto-player")}
        required
        label={__("Title", "presto-player")}
        disabled={saving}
        autoFocus
      />
      <Flex justify="start" align="center">
        <Button
          style={{ margin: 0 }}
          variant="primary"
          isBusy={saving}
          onClick={saving ? () => {} : createVideo}
        >
          {__("Create", "presto-player")}{" "}
          {saving && <Spinner style={{ marginTop: 0 }} />}
        </Button>
        <Button
          variant="tertiary"
          style={{ margin: 0 }}
          isBusy={saving}
          onClick={() => setStep(false)}
        >
          &larr; {__("Go Back", "presto-player")}
        </Button>
      </Flex>
    </Flex>
  ) : (
    <Flex className={className} direction="column" gap={4}>
      <SelectMediaDropdown
        popoverProps={{ placement: "bottom-start" }}
        value={selectedItems}
        onSelect={(video) =>
          setAttributes({ id: video.id, title: video?.title })
        }
        onCreate={() => setStep("create")}
      />
    </Flex>
  );
};

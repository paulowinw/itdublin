import { BaseControl, Button, Modal, SelectControl, TextControl } from "@wordpress/components";
import { store as coreStore } from "@wordpress/core-data";
import { useDispatch } from "@wordpress/data";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { store as noticesStore } from "@wordpress/notices";

import Headers from "./Headers";

export default ({ onClose, webhook }) => {
  const { saveEntityRecord } = useDispatch(coreStore);
  const { createSuccessNotice, createErrorNotice } = useDispatch(noticesStore);
  const [busy, setBusy] = useState(false);

  const sectionCSS = { margin: "0 0 1.5rem" };

  const [form, setForm] = useState(
    webhook || {
      email_name: "email",
      method: "POST",
    }
  );

  const updateForm = (data) => {
    setForm({
      ...(form || {}),
      ...data,
    });
  };

  const { name, url, method, email_name, headers, archived } = form;

  const submit = async (e) => {
    try {
      e.preventDefault();
      setBusy(true);
      await saveEntityRecord("presto-player", "webhook", {
        ...form,
      });
      createSuccessNotice(
        form?.id
          ? __("Webhook updated", "presto-player")
          : __("Webhook created.", "presto-player"),
        {
          type: "snackbar",
        }
      );
      onClose();
    } catch (e) {
      console.error(e);
      createErrorNotice(
        e?.message || __("Something went wrong.", "presto-player"),
        { type: "snackbar" }
      );
    } finally {
      setBusy(false);
    }
  };

  return (
    <Modal
      title={
        form?.id
          ? __("Edit Webhook", "presto-player")
          : __("Add A Webhook", "presto-player")
      }
      onRequestClose={onClose}
      shouldCloseOnClickOutside={false}
    >
      <form onSubmit={submit}>
        <TextControl
          label={__("Name", "presto-player")}
          placeholder={__("Webhook feed name", "presto-player")}
          value={name}
          onChange={(name) => updateForm({ name })}
          required
          autoFocus
        />

        <TextControl
          label={__("Request URL", "presto-player")}
          placeholder={__("Webhook URL", "presto-player")}
          type="url"
          value={url}
          onChange={(url) => updateForm({ url })}
          required
        />

        <SelectControl
          label={__("Request Method", "presto-player")}
          value={method}
          options={[
            { label: "GET", value: "GET" },
            { label: "POST", value: "POST" },
            { label: "PUT", value: "PUT" },
          ]}
          onChange={(method) => updateForm({ method })}
          required
        />

        <TextControl
          label={__("Email Name", "presto-player")}
          placeholder={__("The name (key) of the email sent.", "presto-player")}
          value={email_name}
          onChange={(email_name) => updateForm({ email_name })}
          required
        />

        <div css={sectionCSS}>
          <BaseControl.VisualLabel>
            {__("Headers", "presto-player")}
          </BaseControl.VisualLabel>
          <Headers
            headers={headers}
            setHeaders={(headers) => updateForm({ headers })}
          />
        </div>

        <Button isPrimary type="submit" isBusy={busy}>
          {form?.id
            ? __("Update", "presto-player")
            : __("Create", "presto-player")}
        </Button>
      </form>
    </Modal>
  );
};

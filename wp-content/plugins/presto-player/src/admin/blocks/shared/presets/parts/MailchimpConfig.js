/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { SelectControl, TextControl, Notice } = wp.components;
const { useEffect, useState } = wp.element;
import LoadSelect from "../../components/LoadSelect";

export default ({ options, updateEmailState }) => {
  const [fetching, setFetching] = useState(false);
  const [lists, setLists] = useState([
    { value: null, label: __("Choose an audience", "presto-player") },
  ]);
  const [error, setError] = useState("");

  const fetchLists = async () => {
    setFetching(true);
    try {
      const fetched = await wp.apiFetch({
        path: "presto-player/v1/mailchimp/lists",
      });

      let listOptions = lists;
      (fetched || []).forEach((list) => {
        listOptions = [
          ...listOptions,
          ...[
            {
              value: list.id,
              label: list.name,
            },
          ],
        ];
      });
      setLists(listOptions);
    } catch (e) {
      if (e?.message) {
        setError(e.message);
      }
    } finally {
      setFetching(false);
    }
  };

  useEffect(() => {
    fetchLists();
  }, []);

  if (fetching) {
    return (
      <div>
        <LoadSelect />
        <LoadSelect />
      </div>
    );
  }

  if (error) {
    return (
      <Notice className="presto-notice" status="error" isDismissible={false}>
        {error}
      </Notice>
    );
  }

  return (
    <div>
      <SelectControl
        label={__("Choose an audience", "presto-player")}
        value={options?.provider_list}
        options={lists}
        onChange={(provider_list) => updateEmailState({ provider_list })}
      />
      <TextControl
        label={__("Tag", "presto-player")}
        help={
          <p>
            {__(
              "Give this contact an optional tag when they are added to the list.",
              "presto-player"
            )}
          </p>
        }
        value={options?.provider_tag}
        onChange={(provider_tag) => updateEmailState({ provider_tag })}
      />
    </div>
  );
};

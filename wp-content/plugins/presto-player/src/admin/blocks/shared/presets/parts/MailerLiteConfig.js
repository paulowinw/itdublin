/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { SelectControl, Notice } = wp.components;
const { useEffect, useState } = wp.element;
import LoadSelect from "../../components/LoadSelect";

export default ({ options, updateEmailState }) => {
  const [fetching, setFetching] = useState(false);
  const [groups, setGroups] = useState([
    { value: null, label: __("Choose a group", "presto-player") },
  ]);
  const [error, setError] = useState("");

  const fetchGroups = async () => {
    setFetching(true);
    try {
      const fetched = await wp.apiFetch({
        path: "presto-player/v1/mailerlite/groups",
      });

      let listOptions = groups;
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
      setGroups(listOptions);
    } catch (e) {
      if (e?.message) {
        setError(e.message);
      }
    } finally {
      setFetching(false);
    }
  };

  useEffect(() => {
    fetchGroups();
  }, []);

  if (fetching) {
    return <LoadSelect />;
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
        label={__("Choose a group", "presto-player")}
        value={options?.provider_list}
        options={groups}
        onChange={(provider_list) => updateEmailState({ provider_list })}
      />
    </div>
  );
};

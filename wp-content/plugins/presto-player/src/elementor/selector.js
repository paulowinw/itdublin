export const url = `${prestoEditorData?.root}${prestoEditorData?.wpVersionString}presto-videos`;

export default function ($element) {
  $element.select2({
    ajax: {
      url,
      dataType: "json",
      headers: {
        "X-WP-Nonce": prestoEditorData.nonce,
      },
      data: function (params) {
        return {
          search: params.term,
        };
      },
      processResults: function (data) {
        return {
          results: jQuery.map(data, function (obj) {
            return { id: obj.id, text: obj?.title || "Untitled Video" };
          }),
        };
      },
    },
  });
}

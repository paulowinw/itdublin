import "alpinejs";
import "./settings.css";

window.prestoBBDropdown = ({ nonce }) => {
  return {
    show: false,
    loading: false,
    focus: false,
    search: "",
    video: {
      name: jQuery("#fl-field-video_name").find("input").val() || "",
      id: jQuery("#fl-field-video_id").find("input").val() || "",
      editLink: "",
    },
    items: [],

    async init() {
      console.log("init");
      this.$watch("show", (val) => {
        val && this.fetchVideos();
        this.$nextTick(() => {
          this.$refs.searchbox.focus();
        });
      });
      this.$watch("search", (val) => {
        val && this.show && this.fetchVideos();
      });
      this.$watch("video.id", (val) => {
        jQuery("#fl-field-video_id").find("input").val(val).trigger("change");
        this.video.editLink = "/wp-admin/post.php?post=" + val + "&action=edit";
      });
      this.$watch("video.name", (val) => {
        jQuery("#fl-field-video_name").find("input").val(val).trigger("change");
      });

      const val = jQuery("#fl-field-video_id").find("input").val();
      if (val) {
        this.video.editLink = "/wp-admin/post.php?post=" + val + "&action=edit";
      }
    },

    setVideo(item) {
      this.video.id = item.ID;
      this.video.name = item.post_title;
      this.close();
      FLBuilder.preview.preview();
    },

    fetchVideos() {
      this.loading = true;
      jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        dataType: "json",
        cache: false,
        data: {
          action: "presto_fetch_videos",
          _wpnonce: nonce,
          search: this.search,
        },
        error: function (jqXHR, textStatus, errorThrown) {
          //console.log('init: error HTTP Status['+jqXHR.status+'] '+errorThrown);
        },
        success: ({ data }) => {
          this.items = data;
        },
        complete: () => {
          this.loading = false;
        },
      });
    },

    open() {
      this.show = true;
    },
    close() {
      this.show = false;
    },
    isOpen() {
      return this.show === true;
    },
  };
};

jQuery("window").on("focus", function () {
  jQuery("select[name='video_select']").trigger("change");
});

!(function(a) {
  "function" == typeof define && define.amd
    ? define(["jquery", "moment"], a)
    : a(jQuery, moment);
})(function(a, b) {
  var c = {
    1: "'inci",
    5: "'inci",
    8: "'inci",
    70: "'inci",
    80: "'inci",
    2: "'nci",
    7: "'nci",
    20: "'nci",
    50: "'nci",
    3: "'üncü",
    4: "'üncü",
    100: "'üncü",
    6: "'ncı",
    9: "'uncu",
    10: "'uncu",
    30: "'uncu",
    60: "'ıncı",
    90: "'ıncı"
  };
  (b.updateLocale || b.lang).call(b, "tr", {
    months: "Ocak_Şubat_Mart_Nisan_Mayıs_Haziran_Temmuz_Ağustos_Eylül_Ekim_Kasım_Aralık".split(
      "_"
    ),
    monthsShort: "Oca_Şub_Mar_Nis_May_Haz_Tem_Ağu_Eyl_Eki_Kas_Ara".split("_"),
    weekdays: "Pazar_Pazartesi_Salı_Çarşamba_Perşembe_Cuma_Cumartesi".split(
      "_"
    ),
    weekdaysShort: "Paz_Pts_Sal_Çar_Per_Cum_Cts".split("_"),
    weekdaysMin: "Pz_Pt_Sa_Ça_Pe_Cu_Ct".split("_"),
    longDateFormat: {
      LT: "HH:mm",
      LTS: "LT:ss",
      L: "DD.MM.YYYY",
      LL: "D MMMM YYYY",
      LLL: "D MMMM YYYY LT",
      LLLL: "dddd, D MMMM YYYY LT"
    },
    calendar: {
      sameDay: "[bugün saat] LT",
      nextDay: "[yarın saat] LT",
      nextWeek: "[haftaya] dddd [saat] LT",
      lastDay: "[dün] LT",
      lastWeek: "[geçen hafta] dddd [saat] LT",
      sameElse: "L"
    },
    relativeTime: {
      future: "%s sonra",
      past: "%s önce",
      s: "birkaç saniye",
      m: "bir dakika",
      mm: "%d dakika",
      h: "bir saat",
      hh: "%d saat",
      d: "bir gün",
      dd: "%d gün",
      M: "bir ay",
      MM: "%d ay",
      y: "bir yıl",
      yy: "%d yıl"
    },
    ordinalParse: /\d{1,2}'(inci|nci|üncü|ncı|uncu|ıncı)/,
    ordinal: function(a) {
      if (0 === a) return a + "'ıncı";
      var b = a % 10,
        d = (a % 100) - b,
        e = a >= 100 ? 100 : null;
      return a + (c[b] || c[d] || c[e]);
    },
    week: {dow: 1, doy: 7}
  })/*,
    a.fullCalendar.datepickerLang("tr", "tr", {
      closeText: "kapat",
      prevText: "&#x3C;geri",
      nextText: "ileri&#x3e",
      currentText: "bugün",
      monthNames: [
        "Ocak",
        "Şubat",
        "Mart",
        "Nisan",
        "Mayıs",
        "Haziran",
        "Temmuz",
        "Ağustos",
        "Eylül",
        "Ekim",
        "Kasım",
        "Aralık"
      ],
      monthNamesShort: [
        "Oca",
        "Şub",
        "Mar",
        "Nis",
        "May",
        "Haz",
        "Tem",
        "Ağu",
        "Eyl",
        "Eki",
        "Kas",
        "Ara"
      ],
      dayNames: [
        "Pazar",
        "Pazartesi",
        "Salı",
        "Çarşamba",
        "Perşembe",
        "Cuma",
        "Cumartesi"
      ],
      dayNamesShort: ["Pz", "Pt", "Sa", "Ça", "Pe", "Cu", "Ct"],
      dayNamesMin: ["Pz", "Pt", "Sa", "Ça", "Pe", "Cu", "Ct"],
      weekHeader: "Hf",
      dateFormat: "dd.mm.yy",
      firstDay: 1,
      isRTL: !1,
      showMonthAfterYear: !1,
      yearSuffix: ""
    }),
    a.fullCalendar.lang("tr", {
      buttonText: {
        next: "ileri",
        month: "Ay",
        week: "Hafta",
        day: "Gün",
        list: "Ajanda"
      },
      allDayText: "Tüm gün",
      eventLimitText: "daha fazla"
    });*/
});

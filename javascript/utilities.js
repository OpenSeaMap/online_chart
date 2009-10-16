
			function setCookie(key, value) {
				var expireDate = new Date;
				expireDate.setMonth(expireDate.getMonth() + 6);
				document.cookie = key + "=" + value + ";" + "expires=" + expireDate.toGMTString() + ";";
			}

			function getCookie(argument) {
 				var buff = document.cookie;
				var args = buff.split(";");
				for(i = 0; i < args.length; i++) {
					var a = args[i].split("=");
					if(trim(a[0]) == argument) {
						return trim(a[1]);
					}
				}
				return "-1";
			}

			function checkKeyReturn(e) {
				if (e.keyCode == 13) {
					return true;
				} else {
					return false;
				}
			}

			function trim(buffer) {
				  return buffer.replace (/^\s+/, '').replace (/\s+$/, '');
			}
			
<?php /*

[TopAdminMenu]
Tabs[]=nmimport

# Definition of the custom topmenu
# Note: This is just an example
#
[Topmenu_nmimport]
# # Uses a custom navigation part (See list NavigationPart group above)
NavigationPartIdentifier=ezcontentnavigationpart
Name=Import
Tooltip=Import of the year books were published
# URL[]
URL[default]=import/input
Enabled[]
Enabled[default]=true
Enabled[browse]=false
Enabled[edit]=false
Shown[]
Shown[default]=true
Shown[edit]=true
Shown[navigation]=true
# We don't show it in browse mode
Shown[browse]=false

[NavigationPart]
Part[ezaffiliatenavigationpart]=Partner

*/ ?>
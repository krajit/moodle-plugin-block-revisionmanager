# Changes required in Levelup xp 


## Change 1


`block_xp/classes/local/observer/observer.php`

```
diff
--- /home/ubuntu/upload/observer.php.original
+++ /home/ubuntu/upload/observer.php.modified
@@ -76,5 +76,16 @@
             $cs->collect_event($event);
         }
     }
 
+    /**
+     * Handle revisionmanager rating saved event.
+     *
+     * @param \block_revisionmanager\event\rating_saved $event The event.
+     * @return void
+     */
+    public static function handle_revisionmanager_rating_saved(\block_revisionmanager\event\rating_saved $event) {
+        $cs = \block_xp\di::get('collection_strategy');
+        if ($cs instanceof \block_xp\local\strategy\event_collection_strategy) {
+            $cs->collect_event($event);
+        }
+    }
+
 }
```

## Change 2


`block_xp/classes/local/rule/event_lister.php`

```
diff
--- /home/ubuntu/upload/event_lister.php.original
+++ /home/ubuntu/upload/event_lister.php.modified
@@ -85,6 +85,9 @@
         // Get module events.
         $list = array_merge($list, self::get_events_list_from_plugintype('mod'));
 
+        // Get block events.
+        $list = array_merge($list, self::get_events_list_from_plugintype('block'));
+
         return $list;
     }
 ```